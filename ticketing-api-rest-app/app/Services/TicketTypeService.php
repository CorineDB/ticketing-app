<?php

namespace App\Services;

use App\Repositories\Contracts\TicketTypeRepositoryContract;
use App\Services\Contracts\PaymentServiceContract;
use App\Services\Contracts\TicketTypeServiceContract;
use App\Services\Core\Eloquent\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketTypeService extends BaseService implements TicketTypeServiceContract
{
    protected PaymentServiceContract $paymentService;

    public function __construct(
        TicketTypeRepositoryContract $repository,
        PaymentServiceContract $paymentService
    ) {
        parent::__construct($repository);
        $this->paymentService = $paymentService;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $ticketType = $this->repository->create($data);

            // Générer automatiquement le lien de paiement si le prix > 0
            if ($ticketType->price > 0) {
                try {
                    $paymentLink = $this->paymentService->createPaymentLinkForTicketType($ticketType->id);

                    $this->repository->update($ticketType, [
                        'payment_url' => $paymentLink['payment_url'],
                        'payment_transaction_id' => $paymentLink['transaction_id'],
                        'payment_token' => $paymentLink['token'],
                    ]);

                    $ticketType = $ticketType->fresh();
                } catch (\Exception $e) {
                    Log::error('Failed to create payment link for ticket type', [
                        'ticket_type_id' => $ticketType->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Ne pas bloquer la création du ticket type si le paiement échoue
                }
            }

            return $ticketType;
        });
    }

    public function getByEvent(string $eventId)
    {
        return $this->repository->findByEvent($eventId);
    }

    public function checkQuotaAvailability(string $ticketTypeId): bool
    {
        return $this->repository->checkQuotaAvailability($ticketTypeId);
    }
}
