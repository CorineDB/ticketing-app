<?php

namespace App\Services;

use App\Repositories\Contracts\TicketRepositoryContract;
use App\Repositories\Contracts\TicketTypeRepositoryContract;
use App\Services\Contracts\TicketServiceContract;
use App\Services\Core\Eloquent\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketService extends BaseService implements TicketServiceContract
{
    protected TicketTypeRepositoryContract $ticketTypeRepository;

    public function __construct(
        TicketRepositoryContract $repository,
        TicketTypeRepositoryContract $ticketTypeRepository
    ) {
        parent::__construct($repository);
        $this->ticketTypeRepository = $ticketTypeRepository;
    }

    public function generateTicket(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['ticket_type_id'])) {
                if (!$this->ticketTypeRepository->checkQuotaAvailability($data['ticket_type_id'])) {
                    throw new \Exception('QUOTA_EXCEEDED: Ticket quota has been reached', 422);
                }
            }

            $data['code'] = $this->repository->generateUniqueCode($data['event_id']);
            $data['issued_at'] = now();
            $data['status'] = $data['status'] ?? 'issued';

            $data['magic_link_token'] = Str::random(64);

            $ticket = $this->repository->create($data);

            $this->generateQRCodeForTicket($ticket);

            return $ticket->fresh();
        });
    }

    public function generateBulkTickets(array $data)
    {
        $quantity = $data['quantity'] ?? 1;
        $tickets = [];

        for ($i = 0; $i < $quantity; $i++) {
            $tickets[] = $this->generateTicket($data);
        }

        return $tickets;
    }

    public function getByCode(string $code)
    {
        return $this->repository->findByCode($code);
    }

    public function getByEvent(string $eventId)
    {
        return $this->repository->findByEvent($eventId);
    }

    public function getByMagicLink(string $token)
    {
        return $this->repository->findByMagicLinkToken($token);
    }

    public function generateQRCode(string $ticketId)
    {
        $ticket = $this->repository->findOrFail($ticketId);
        return $this->generateQRCodeForTicket($ticket);
    }

    protected function generateQRCodeForTicket($ticket)
    {
        $signature = $this->generateHMACSignature($ticket->id, $ticket->event_id);

        $qrData = config('app.url') . "/t/{$ticket->id}?sig={$signature}";

        $qrImage = QrCode::format('png')
            ->size(300)
            ->generate($qrData);

        $filename = "tickets/qr/{$ticket->id}.png";

        Storage::disk('public')->put($filename, $qrImage);

        $qrPath = Storage::disk('public')->url($filename);

        $this->repository->update($ticket, [
            'qr_path' => $qrPath,
            'qr_hmac' => $signature,
        ]);

        return [
            'qr_path' => $qrPath,
            'qr_hmac' => $signature,
            'magic_link_token' => $ticket->magic_link_token,
        ];
    }

    protected function generateHMACSignature(string $ticketId, string $eventId): string
    {
        $secret = config('app.ticket_hmac_secret', config('app.key'));
        return hash_hmac('sha256', $ticketId . '|' . $eventId, $secret);
    }

    public function markAsPaid(string $ticketId)
    {
        return DB::transaction(function () use ($ticketId) {
            $ticket = $this->repository->findOrFail($ticketId);

            return $this->repository->update($ticket, [
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        });
    }

    public function invalidateTicket(string $ticketId, string $reason)
    {
        return DB::transaction(function () use ($ticketId, $reason) {
            $ticket = $this->repository->findOrFail($ticketId);

            $metadata = $ticket->metadata ?? [];
            $metadata['invalidation_reason'] = $reason;
            $metadata['invalidated_at'] = now()->toISOString();

            return $this->repository->update($ticket, [
                'status' => 'invalid',
                'metadata' => $metadata,
            ]);
        });
    }
}
