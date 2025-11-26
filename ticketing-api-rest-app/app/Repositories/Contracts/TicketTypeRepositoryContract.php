<?php

namespace App\Repositories\Contracts;

use App\Repositories\Core\Contracts\BaseRepositoryInterface;

interface TicketTypeRepositoryContract extends BaseRepositoryInterface
{
    public function findByEvent(string $eventId);

    public function checkQuotaAvailability(string $ticketTypeId): bool;
}
