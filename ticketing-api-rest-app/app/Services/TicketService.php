<?php

namespace App\Services;

use App\Repositories\Contracts\TicketRepositoryContract;
use App\Repositories\Contracts\TicketTypeRepositoryContract;
use App\Services\Contracts\NotificationServiceContract;
use App\Services\Contracts\TicketServiceContract;
use App\Services\Core\Eloquent\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketService extends BaseService implements TicketServiceContract
{
    protected TicketTypeRepositoryContract $ticketTypeRepository;
    protected NotificationServiceContract $notificationService;

    public function __construct(
        TicketRepositoryContract $repository,
        TicketTypeRepositoryContract $ticketTypeRepository,
        NotificationServiceContract $notificationService
    ) {
        parent::__construct($repository);
        $this->ticketTypeRepository = $ticketTypeRepository;
        $this->notificationService = $notificationService;
    }

    /* public function list(int $limit = 15, $relations = [])
    {
        return $this->paginate($limit, $relations);
        return $this->repository->query()->with($relations)->paginate($limit, ['*']);
    } */

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
            if ($data['status'] === 'paid') {
                $this->repository->update($ticket, [
                    'paid_at' => now(),
                ]);
                // Send ticket confirmation email
                $this->notificationService->sendTicketConfirmation($ticket->id);
            }

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

        // QR code pointe vers le frontend pour le scan
        $frontendUrl = config('app.frontend_url', env('CLIENT_APP_URL', 'http://localhost:5173'));
        $qrData = $frontendUrl . "/dashboard/scan?t={$ticket->id}&sig={$signature}";

        $qrImage = QrCode::format('png')
            ->size(300)
            ->generate($qrData);

        $filename = "tickets/qr/{$ticket->id}.png";

        // Stockage sur disque local (privé)
        Storage::disk('local')->put($filename, $qrImage);

        $this->repository->update($ticket, [
            'qr_path' => $filename,  // Chemin relatif, pas URL publique
            'qr_hmac' => $signature,
        ]);

        // This is the URL that the front should use to display the QR
        $downloadUrl = url("/api/tickets/{$ticket->id}/qr/download?token={$ticket->magic_link_token}");

        return [
            'qr_path' => $filename,
            'url' => $downloadUrl,
            'qr_hmac' => $signature,
            'magic_link_token' => $ticket->magic_link_token,
        ];
    }

    public function getQRCodeFile(string $ticketId)
    {
        $ticket = $this->repository->findOrFail($ticketId);

        if (!$ticket->qr_path || !Storage::disk('local')->exists($ticket->qr_path)) {
            throw new \Exception('QR code file not found', 404);
        }

        return [
            'path' => $ticket->qr_path,
            'content' => Storage::disk('local')->get($ticket->qr_path),
            'mime_type' => 'image/png',
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
    public function sendTicketByEmail(string $ticketId)
    {
        $ticket = $this->repository->findOrFail($ticketId);

        $this->notificationService->sendTicketConfirmation($ticket->id);

        return $ticket;
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
    /**
     * Get ticket type by ID
     */
    public function getTicketType(string $ticketTypeId)
    {
        return $this->ticketTypeRepository->find($ticketTypeId);
    }

    /**
     * Check if quota is available for a ticket type
     */
    public function checkQuotaAvailability(string $ticketTypeId, int $quantity = 1): bool
    {
        $ticketType = $this->ticketTypeRepository->find($ticketTypeId);

        if (!$ticketType) {
            return false;
        }

        // Count sold tickets for this ticket type
        $soldCount = $this->repository->countByTicketTypeAndStatuses(
            $ticketTypeId,
            ['issued', 'reserved', 'paid', 'in', 'out']
        );

        $available = $ticketType->quota - $soldCount;

        return $available >= $quantity;
    }
}
