<?php

namespace App\Services;

use App\Repositories\Contracts\TicketRepositoryContract;
use App\Repositories\Contracts\TicketTypeRepositoryContract;
use App\Services\Contracts\NotificationServiceContract;
use App\Services\Contracts\PaymentServiceContract;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use FedaPay\Webhook;
use Illuminate\Support\Facades\Log;

class PaymentService implements PaymentServiceContract
{
    protected TicketTypeRepositoryContract $ticketTypeRepository;
    protected TicketRepositoryContract $ticketRepository;
    protected NotificationServiceContract $notificationService;

    public function __construct(
        TicketTypeRepositoryContract $ticketTypeRepository,
        TicketRepositoryContract $ticketRepository,
        NotificationServiceContract $notificationService
    ) {
        $this->ticketTypeRepository = $ticketTypeRepository;
        $this->ticketRepository = $ticketRepository;
        $this->notificationService = $notificationService;

        // Initialiser FedaPay
        FedaPay::setApiKey(config('services.fedapay.secret_key'));
        FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));
    }

    public function createPaymentLinkForTicketType(string $ticketTypeId): array
    {
        $ticketType = $this->ticketTypeRepository->findOrFail($ticketTypeId);

        try {
            // Créer une transaction FedaPay
            $transaction = Transaction::create([
                'description' => "Ticket: {$ticketType->name}",
                'amount' => $ticketType->price,
                'currency' => ['iso' => config('services.fedapay.currency', 'XOF')],
                'callback_url' => route('payment.callback'),
                'custom_metadata' => [
                    'ticket_type_id' => $ticketType->id,
                    'event_id' => $ticketType->event_id,
                    'ticket_type_name' => $ticketType->name,
                ],
            ]);

            // Générer le token de paiement
            $token = $transaction->generateToken();

            return [
                'transaction_id' => $transaction->id,
                'token' => $token->token,
                'payment_url' => $token->url,
                'amount' => $ticketType->price,
                'currency' => config('services.fedapay.currency', 'XOF'),
            ];
        } catch (\Exception $e) {
            Log::error('FedaPay payment link creation failed', [
                'ticket_type_id' => $ticketTypeId,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create payment link: ' . $e->getMessage());
        }
    }

    public function createTransaction(array $data): array
    {
        try {
            $transaction = Transaction::create([
                'description' => $data['description'] ?? 'Ticket Purchase',
                'amount' => $data['amount'],
                'currency' => ['iso' => $data['currency'] ?? config('services.fedapay.currency', 'XOF')],
                'callback_url' => $data['callback_url'] ?? route('payment.callback'),
                'custom_metadata' => $data['metadata'] ?? [],
            ]);

            $token = $transaction->generateToken();

            return [
                'transaction_id' => $transaction->id,
                'token' => $token->token,
                'payment_url' => $token->url,
                'amount' => $data['amount'],
            ];
        } catch (\Exception $e) {
            Log::error('FedaPay transaction creation failed', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create transaction: ' . $e->getMessage());
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $endpointSecret = config('services.fedapay.webhook_secret');

            if (!$endpointSecret) {
                Log::warning('FedaPay webhook secret not configured');
                return false;
            }

            // Vérifier la signature du webhook
            Webhook::constructEvent($payload, $signature, $endpointSecret);

            return true;
        } catch (\Exception $e) {
            Log::error('FedaPay webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function handleWebhookEvent(array $eventData): void
    {
        $eventType = $eventData['name'] ?? null;
        $entity = $eventData['entity'] ?? [];

        Log::info('FedaPay webhook received', [
            'event_type' => $eventType,
            'entity_id' => $entity['id'] ?? null,
        ]);

        switch ($eventType) {
            case 'transaction.approved':
                $this->handleTransactionApproved($entity);
                break;

            case 'transaction.canceled':
                $this->handleTransactionCanceled($entity);
                break;

            case 'transaction.created':
                $this->handleTransactionCreated($entity);
                break;

            default:
                Log::info('Unhandled FedaPay webhook event', ['event_type' => $eventType]);
        }
    }

    protected function handleTransactionApproved(array $entity): void
    {
        $metadata = $entity['custom_metadata'] ?? [];
        $ticketId = $metadata['ticket_id'] ?? null;

        if (!$ticketId) {
            Log::warning('Transaction approved but no ticket_id in metadata', ['entity' => $entity]);
            return;
        }

        try {
            $ticket = $this->ticketRepository->find($ticketId);

            if ($ticket) {
                $this->ticketRepository->update($ticket, [
                    'status' => 'paid',
                    'paid_at' => now(),
                    'metadata' => array_merge($ticket->metadata ?? [], [
                        'fedapay_transaction_id' => $entity['id'],
                        'fedapay_reference' => $entity['reference'] ?? null,
                        'payment_approved_at' => now()->toISOString(),
                    ]),
                ]);

                Log::info('Ticket marked as paid', [
                    'ticket_id' => $ticketId,
                    'transaction_id' => $entity['id'],
                ]);

                // Envoyer notification de paiement confirmé
                $this->notificationService->sendPaymentConfirmation($ticketId, [
                    'transaction_id' => $entity['id'],
                    'reference' => $entity['reference'] ?? null,
                    'amount' => $entity['amount'] ?? 0,
                    'currency' => $entity['currency']['iso'] ?? 'XOF',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to update ticket after payment approval', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function handleTransactionCanceled(array $entity): void
    {
        $metadata = $entity['custom_metadata'] ?? [];
        $ticketId = $metadata['ticket_id'] ?? null;

        if ($ticketId) {
            Log::info('Transaction canceled for ticket', [
                'ticket_id' => $ticketId,
                'transaction_id' => $entity['id'],
            ]);
        }
    }

    protected function handleTransactionCreated(array $entity): void
    {
        Log::info('Transaction created', ['transaction_id' => $entity['id']]);
    }
}
