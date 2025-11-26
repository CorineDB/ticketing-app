<?php

namespace App\Services\Contracts;

interface PaymentServiceContract
{
    public function createPaymentLinkForTicketType(string $ticketTypeId): array;

    public function createTransaction(array $data): array;

    public function verifyWebhookSignature(string $payload, string $signature): bool;

    public function handleWebhookEvent(array $eventData): void;
}
