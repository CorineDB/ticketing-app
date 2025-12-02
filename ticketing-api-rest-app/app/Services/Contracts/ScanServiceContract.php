<?php

namespace App\Services\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface ScanServiceContract
{
    public function requestScan(string $ticketId, string $signature): array;

    public function confirmScan(string $sessionToken, string $nonce, string $gateId, string $agentId, string $action): array;

    public function validateTicketSignature(string $ticketId, string $signature): bool;

    public function getScanHistory(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}
