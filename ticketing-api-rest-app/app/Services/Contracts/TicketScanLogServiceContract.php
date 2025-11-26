<?php

namespace App\Services\Contracts;

use App\Services\Core\Contracts\BaseServiceInterface;

interface TicketScanLogServiceContract extends BaseServiceInterface
{
    public function getByTicket(string $ticketId);

    public function getByGate(string $gateId);

    public function getByAgent(string $agentId);

    public function logScan(array $data);
}
