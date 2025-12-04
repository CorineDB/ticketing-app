<?php

namespace App\Services;

use App\Repositories\Contracts\EventCounterRepositoryContract;
use App\Repositories\Contracts\EventRepositoryContract;
use App\Repositories\Contracts\GateRepositoryContract;
use App\Repositories\Contracts\TicketRepositoryContract;
use App\Repositories\Contracts\TicketScanLogRepositoryContract;
use App\Services\Contracts\NotificationServiceContract;
use App\Services\Contracts\ScanServiceContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ScanService implements ScanServiceContract
{
    protected TicketRepositoryContract $ticketRepository;
    protected EventRepositoryContract $eventRepository;
    protected GateRepositoryContract $gateRepository;
    protected TicketScanLogRepositoryContract $scanLogRepository;
    protected EventCounterRepositoryContract $counterRepository;
    protected NotificationServiceContract $notificationService;

    public function __construct(
        TicketRepositoryContract $ticketRepository,
        EventRepositoryContract $eventRepository,
        GateRepositoryContract $gateRepository,
        TicketScanLogRepositoryContract $scanLogRepository,
        EventCounterRepositoryContract $counterRepository,
        NotificationServiceContract $notificationService
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->eventRepository = $eventRepository;
        $this->gateRepository = $gateRepository;
        $this->scanLogRepository = $scanLogRepository;
        $this->counterRepository = $counterRepository;
        $this->notificationService = $notificationService;
    }

    public function requestScan(string $ticketId, string $signature): array
    {
        $ticket = $this->ticketRepository->find($ticketId);

        if (!$ticket) {
            throw new \Exception('TICKET_NOT_FOUND', 404);
        }

        if (!$this->validateTicketSignature($ticketId, $signature)) {
            throw new \Exception('QR_SIGNATURE_MISMATCH: Invalid QR signature', 400);
        }

        $sessionToken = Str::random(64);
        $nonce = Str::random(32);
        $expiresIn = 20;

        Cache::put("scan_session:{$sessionToken}", [
            'ticket_id' => $ticketId,
            'nonce' => $nonce,
        ], now()->addSeconds($expiresIn));

        // Charger les relations pour afficher les infos dans le frontend
        $ticket->load(['event', 'ticketType']);

        return [
            'scan_session_token' => $sessionToken,
            'scan_nonce' => $nonce,
            'expires_in' => $expiresIn,
            'ticket' => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'status' => $ticket->status,
                'buyer_name' => $ticket->buyer_name,
                'buyer_email' => $ticket->buyer_email,
                'buyer_phone' => $ticket->buyer_phone,
                'event' => [
                    'id' => $ticket->event->id,
                    'title' => $ticket->event->title,
                    'start_datetime' => $ticket->event->start_datetime,
                    'end_datetime' => $ticket->event->end_datetime,
                ],
                'ticket_type' => [
                    'id' => $ticket->ticketType->id,
                    'name' => $ticket->ticketType->name,
                    'price' => $ticket->ticketType->price,
                ],
            ],
        ];
    }

    public function confirmScan(string $sessionToken, string $nonce, string $gateId, string $agentId, string $action): array
    {
        $sessionData = Cache::get("scan_session:{$sessionToken}");

        if (!$sessionData) {
            throw new \Exception('CONFLICT_SCAN: Session expired or invalid', 409);
        }

        if ($sessionData['nonce'] !== $nonce) {
            throw new \Exception('CONFLICT_SCAN: Invalid nonce', 409);
        }

        Cache::forget("scan_session:{$sessionToken}");

        $ticketId = $sessionData['ticket_id'];
        $lockKey = "ticket_scan_lock:{$ticketId}";
        $lock = Cache::lock($lockKey, 5);

        if (!$lock->get()) {
            throw new \Exception('CONFLICT_SCAN: Ticket is currently being processed', 409);
        }

        try {
            // On exécute la transaction et on stocke le résultat
            $result = DB::transaction(function () use ($ticketId, $gateId, $agentId, $action) {
                try {
                    return $this->processScan($ticketId, $gateId, $agentId, $action);
                } catch (\Throwable $th) {
                    \Log::error('Throwable caught inside DB::transaction in processScan:', [
                        'message' => $th->getMessage(),
                        'file' => $th->getFile(),
                        'line' => $th->getLine(),
                        'trace' => $th->getTraceAsString(),
                    ]);
                    throw $th; // Re-throw the original Throwable
                }
            });

            // On s'assure que le résultat est bien un tableau avant de le retourner
            if (!is_array($result)) {
                // Si ce n'est pas un tableau, on log l'erreur et on retourne une erreur standard
                \Log::error('ScanService::confirmScan did not return an array.', ['result' => $result]);
                throw new \Exception('INTERNAL_SERVER_ERROR: Invalid response from scan process', 500);
            }

            return $result;

        } catch (\Throwable $e) {
            \Log::error('Throwable caught in ScanService::confirmScan:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('INTERNAL_SERVER_ERROR: An unexpected error occurred during scan confirmation.', 500, $e); // Re-throw as a standard Exception
        } finally {
            // Le verrou est libéré quoi qu'il arrive
            $lock->release();
        }
    }

    protected function processScan(string $ticketId, string $gateId, string $agentId, string $action): array
    {
        $ticket = $this->ticketRepository->findOrFail($ticketId);
        $event = $this->eventRepository->findOrFail($ticket->event_id);
        $gate = $this->gateRepository->findOrFail($gateId);

        // Règle métier : Vérifier que l'événement est en cours
        if (now()->lt($event->start_datetime)) {
            return $this->logAndReturnScanResult($ticketId, $agentId, $gateId, $action, 'invalid', [
                'message' => 'Event has not started yet',
                'event_starts_at' => $event->start_datetime->toISOString(),
            ], $ticket);
        }

        if (now()->gt($event->end_datetime)) {
            return $this->logAndReturnScanResult($ticketId, $agentId, $gateId, $action, 'expired', [
                'message' => 'Event has already ended',
                'event_ended_at' => $event->end_datetime->toISOString(),
            ], $ticket);
        }

        if ($gate->status !== 'active') {
            return $this->logAndReturnScanResult($ticketId, $agentId, $gateId, $action, 'invalid', [
                'message' => 'Gate is not active',
            ], $ticket);
        }

        if (!in_array($ticket->status, ['paid', 'in', 'out'])) {
            return $this->logAndReturnScanResult($ticketId, $agentId, $gateId, $action, 'invalid', [
                'message' => 'Ticket status is invalid for scanning',
                'current_status' => $ticket->status,
            ], $ticket);
        }

        if ($ticket->ticket_type_id) {
            $ticketType = $this->ticketRepository->find($ticket->ticket_type_id);
            if ($ticketType) {
                if ($ticketType->validity_from && now()->lt($ticketType->validity_from)) {
                    return $this->logAndReturnScanResult($ticketId, $agentId, $gateId, $action, 'expired', [
                        'message' => 'Ticket not yet valid',
                    ], $ticket);
                }

                if ($ticketType->validity_to && now()->gt($ticketType->validity_to)) {
                    return $this->logAndReturnScanResult($ticketId, $agentId, $gateId, $action, 'expired', [
                        'message' => 'Ticket has expired',
                    ], $ticket);
                }
            }
        }

        if ($action === 'in' || $action === 'entry') {
            return $this->processEntry($ticket, $event, $gateId, $agentId);
        } elseif ($action === 'out' || $action === 'exit') {
            return $this->processExit($ticket, $event, $gateId, $agentId);
        }

        throw new \Exception('Invalid action', 400);
    }

    protected function processEntry($ticket, $event, $gateId, $agentId): array
    {
        if ($ticket->status === 'in') {
            return $this->logAndReturnScanResult(
                $ticket->id,
                $agentId,
                $gateId,
                'entry',
                'already_in',
                ['message' => 'Ticket is already inside'],
                $ticket
            );
        }

        // Règle métier : Vérifier allow_reentry pour les re-entrées
        if ($ticket->status === 'out') {
            if (!$event->allow_reentry) {
                return $this->logAndReturnScanResult(
                    $ticket->id,
                    $agentId,
                    $gateId,
                    'entry',
                    'invalid',
                    ['message' => 'Re-entry is not allowed for this event'],
                    $ticket
                );
            }

            // Règle métier : Cooldown de 60 secondes après sortie (anti-fraude)
            $cooldownSeconds = 60;
            if ($ticket->last_used_at && now()->diffInSeconds($ticket->last_used_at) < $cooldownSeconds) {
                $remainingSeconds = $cooldownSeconds - now()->diffInSeconds($ticket->last_used_at);
                return $this->logAndReturnScanResult(
                    $ticket->id,
                    $agentId,
                    $gateId,
                    'entry',
                    'invalid',
                    [
                        'message' => 'Re-entry cooldown active',
                        'wait_seconds' => $remainingSeconds,
                    ],
                    $ticket
                );
            }
        }

        $this->counterRepository->createOrGetCounter($event->id);
        $currentIn = $this->counterRepository->getCurrentIn($event->id);

        if ($currentIn >= $event->capacity) {
            return $this->logAndReturnScanResult(
                $ticket->id,
                $agentId,
                $gateId,
                'entry',
                'capacity_full',
                [
                    'message' => 'Event capacity reached',
                    'current_in' => $currentIn,
                    'capacity' => $event->capacity,
                ],
                $ticket
            );
        }

        if ($ticket->ticket_type_id) {
            $ticketType = $this->ticketRepository->find($ticket->ticket_type_id);
            if ($ticketType && $ticketType->usage_limit) {
                if ($ticket->used_count >= $ticketType->usage_limit) {
                    return $this->logAndReturnScanResult(
                        $ticket->id,
                        $agentId,
                        $gateId,
                        'entry',
                        'invalid',
                        ['message' => 'Usage limit reached'],
                        $ticket
                    );
                }
            }
        }

        $this->counterRepository->incrementCurrentIn($event->id);

        $this->ticketRepository->update($ticket, [
            'status' => 'in',
            'used_count' => $ticket->used_count + 1,
            'last_used_at' => now(),
            'gate_in' => $gateId,
        ]);

        $gate = $this->gateRepository->find($gateId);

        // Envoyer notification d'entrée
        $this->notificationService->sendScanNotification($ticket->id, 'in', [
            'gate_name' => $gate ? $gate->name : 'N/A',
            'scan_time' => now(),
        ]);

        return $this->logAndReturnScanResult(
            $ticket->id,
            $agentId,
            $gateId,
            'entry',
            'ok',
            ['message' => 'Entry successful'],
            $ticket->fresh()
        );
    }

    protected function processExit($ticket, $event, $gateId, $agentId): array
    {
        if ($ticket->status !== 'in') {
            return $this->logAndReturnScanResult(
                $ticket->id,
                $agentId,
                $gateId,
                'exit',
                'already_out',
                ['message' => 'Ticket is not currently inside'],
                $ticket
            );
        }

        $this->counterRepository->decrementCurrentIn($event->id);

        $this->ticketRepository->update($ticket, [
            'status' => 'out',
            'last_used_at' => now(),
            'last_gate_out' => $gateId,
        ]);

        $gate = $this->gateRepository->find($gateId);

        // Envoyer notification de sortie
        $this->notificationService->sendScanNotification($ticket->id, 'out', [
            'gate_name' => $gate ? $gate->name : 'N/A',
            'scan_time' => now(),
        ]);

        return $this->logAndReturnScanResult(
            $ticket->id,
            $agentId,
            $gateId,
            'exit',
            'ok',
            ['message' => 'Exit successful'],
            $ticket->fresh()
        );
    }

    protected function logAndReturnScanResult($ticketId, $agentId, $gateId, $scanType, $result, $details, $ticket): array
    {
        $scanLog = $this->scanLogRepository->create([
            'ticket_id' => $ticketId,
            'agent_id' => $agentId,
            'gate_id' => $gateId,
            'scan_type' => $scanType,
            'scan_time' => now(),
            'result' => $result,
            'details' => $details,
            'metadata' => [],
        ]);

        return [
            'valid' => $result === 'ok',
            'code' => strtoupper($result),
            'message' => $details['message'] ?? $result,
            'ticket' => [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'status' => $ticket->status,
                'buyer_name' => $ticket->buyer_name,
                'buyer_email' => $ticket->buyer_email,
                'buyer_phone' => $ticket->buyer_phone,
                'event_id' => $ticket->event_id,
                'ticket_type_id' => $ticket->ticket_type_id,
            ],
            'scan_log_id' => $scanLog->id,
        ];
    }

    public function validateTicketSignature(string $ticketId, string $signature): bool
    {
        $ticket = $this->ticketRepository->find($ticketId);

        if (!$ticket) {
            return false;
        }

        $secret = config('app.ticket_hmac_secret', config('app.key'));
        $metadata = $ticket->metadata ?? [];
        $nonce = $metadata['qr_nonce'] ?? '';
        $expectedSignature = hash_hmac('sha256', $ticketId . '|' . $ticket->event_id . '|' . $nonce, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    public function getScanHistory(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $user = auth()->user();
        $query = $this->scanLogRepository->query();

        // Role-based filtering
        if ($user && $user->isSuperAdmin()) {
            // Super Admin sees all
        } elseif ($user && $user->isOrganizer()) {
            // Organizer sees scans for their events
            $query->whereHas('ticket.event', function ($q) use ($user) {
                $q->where('organisateur_id', $user->id);
            });
        } elseif ($user && $user->isAgent()) {
            // Agent sees only their own scans
            $query->where('agent_id', $user->id);
        } else {
            // Others see nothing
            $query->whereRaw('1 = 0');
        }

        if (isset($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        }
        if (isset($filters['gate_id'])) {
            $query->where('gate_id', $filters['gate_id']);
        }
        if (isset($filters['scanner_id'])) {
            $query->where('agent_id', $filters['scanner_id']);
        }
        if (isset($filters['scan_type'])) {
            $query->where('scan_type', $filters['scan_type']);
        }
        if (isset($filters['result'])) {
            $query->where('result', $filters['result']);
        }
        if (isset($filters['start_date'])) {
            $query->where('scan_time', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('scan_time', '<=', $filters['end_date']);
        }
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('ticket_id', 'like', "%{$search}%")
                    ->orWhereHas('ticket', function ($q) use ($search) {
                        $q->where('buyer_name', 'like', "%{$search}%")
                            ->orWhere('buyer_email', 'like', "%{$search}%");
                    });
            });
        }

        $query->with(['ticket', 'ticket.event', 'agent', 'gate']);

        // Default sort
        $query->orderBy('scan_time', 'desc');

        return $query->paginate($perPage);
    }
}
