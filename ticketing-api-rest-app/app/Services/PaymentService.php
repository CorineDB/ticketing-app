<?php

namespace App\Services;

use App\Repositories\Contracts\TicketRepositoryContract;
use App\Repositories\Contracts\TicketTypeRepositoryContract;
use App\Services\Contracts\NotificationServiceContract;
use App\Services\Contracts\PaymentServiceContract;
use FedaPay\Customer;
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

    /**
     * Create a payment transaction for a specific ticket purchase
     *
     * @param array $ticketIds Array of ticket IDs for this purchase
     * @param array $customerData Customer information (firstname, lastname, email, phone_number)
     * @param int $amount Total amount
     * @param string $description Transaction description
     * @return array Payment information including payment_url
     */
    public function createTransactionForTicket(array $ticketIds, array $customerData, int $amount, string $description): array
    {
        try {
            // Prepare customer data
            $fedapayCustomerData = [
                'firstname' => $customerData['firstname'],
                'lastname' => $customerData['lastname'],
                'email' => $customerData['email'],
            ];

            // Only add phone_number if provided and looks valid
            if (!empty($customerData['phone_number'])) {
                $phoneNumber = $customerData['phone_number'];

                // Detect country based on phone number format
                $country = 'BJ'; // Default to Benin

                // Benin international format: +229XXXXXXXXXX or 229XXXXXXXXXX (10 digits after country code)
                if (preg_match('/^\+?229([0-9]{10})$/', $phoneNumber, $matches)) {
                    $phoneNumber = ltrim($matches[1], '0'); // Extract the 10 digits and remove leading zero
                    $country = 'BJ';
                } elseif (preg_match('/^([69][0-9]{7})$/', $phoneNumber, $matches)) {
                    // Benin local format: 6XXXXXXXX or 9XXXXXXXX (8 digits)
                    $phoneNumber = $matches[1]; // Extract the 8 digits
                    $country = 'BJ';
                } elseif (preg_match('/^\+?33([0-9]{9})$/', $phoneNumber, $matches)) {
                    // French international format: +33XXXXXXXXX or 33XXXXXXXXX (9 digits after country code)
                    $phoneNumber = $matches[1]; // Extract the 9 digits
                    $country = 'FR';
                } elseif (preg_match('/^0([1-9][0-9]{8})$/', $phoneNumber, $matches)) {
                    // French local format: 0XXXXXXXXX (10 digits)
                    $phoneNumber = $matches[1]; // Extract the 9 digits without the leading 0
                    $country = 'FR';
                } else {
                    // If format is unclear, skip phone number to avoid FedaPay error
                    Log::warning('Phone number format not recognized, skipping for FedaPay customer', [
                        'phone_number' => $phoneNumber
                    ]);
                    $phoneNumber = null;
                }

                if ($phoneNumber) {
                    $fedapayCustomerData['phone_number'] = [
                        'number' => $phoneNumber,
                        'country' => $country,
                    ];
                }
            }

            Log::debug('FedaPay customer data before creation', $fedapayCustomerData);

            // Create or get FedaPay customer
            $customer = Customer::create($fedapayCustomerData);

            // Créer une transaction FedaPay
            $transaction = Transaction::create([
                'description' => $description,
                'amount' => $amount,
                "mode" => "bank_transfer",
                "provider" => "visa",
                'currency' => ['iso' => config('services.fedapay.currency', 'XOF')],
                'callback_url' => route('payment.callback'),
                'customer' => ['id' => $customer->id],
                'merchant_reference' => 'tickets-' . implode('-', $ticketIds),
                'custom_metadata' => [
                    'ticket_ids' => $ticketIds, // Array of all ticket IDs
                    'ticket_count' => count($ticketIds),
                ],
            ]);

            // Générer le token de paiement
            $token = $transaction->generateToken();

            return [
                'transaction_id' => $transaction->id,
                'token' => $token->token,
                'payment_url' => $token->url,
                'amount' => $amount,
                'currency' => config('services.fedapay.currency', 'XOF'),
            ];
        } catch (\Exception $e) {
            Log::error('FedaPay transaction creation failed', [
                'ticket_ids' => $ticketIds,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to create payment transaction: ' . $e->getMessage());
        }
    }

    /**
     * @deprecated This method is deprecated. Use createTransactionForTicket instead.
     */
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
        $ticketIds = $metadata['ticket_ids'] ?? null;

        // Support for old single ticket_id format (backward compatibility)
        if (!$ticketIds && isset($metadata['ticket_id'])) {
            $ticketIds = [$metadata['ticket_id']];
        }

        if (!$ticketIds || !is_array($ticketIds)) {
            Log::warning('Transaction approved but no ticket_ids in metadata', ['entity' => $entity]);
            return;
        }

        try {
            $updatedCount = 0;

            foreach ($ticketIds as $ticketId) {
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

                    $updatedCount++;

                    // Envoyer notification de paiement confirmé pour le premier ticket seulement
                    if ($updatedCount === 1) {
                        $this->notificationService->sendPaymentConfirmation($ticketId, [
                            'transaction_id' => $entity['id'],
                            'reference' => $entity['reference'] ?? null,
                            'amount' => $entity['amount'] ?? 0,
                            'currency' => $entity['currency']['iso'] ?? 'XOF',
                            'ticket_count' => count($ticketIds),
                        ]);
                    }
                }
            }

            Log::info('Tickets marked as paid', [
                'ticket_ids' => $ticketIds,
                'updated_count' => $updatedCount,
                'transaction_id' => $entity['id'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update tickets after payment approval', [
                'ticket_ids' => $ticketIds,
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
