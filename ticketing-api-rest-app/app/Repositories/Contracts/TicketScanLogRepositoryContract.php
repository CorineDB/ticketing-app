<?php

namespace App\Repositories\Contracts;

use App\Repositories\Core\Contracts\BaseRepositoryInterface;

interface TicketScanLogRepositoryContract extends BaseRepositoryInterface
{
    public function findByTicket(string $ticketId);

    public function findByGate(string $gateId);

    public function findByAgent(string $agentId);
}
