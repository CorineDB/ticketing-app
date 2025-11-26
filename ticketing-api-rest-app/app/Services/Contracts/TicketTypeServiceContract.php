<?php

namespace App\Services\Contracts;

use App\Services\Core\Contracts\BaseServiceInterface;

interface TicketTypeServiceContract extends BaseServiceInterface
{
    public function getByEvent(string $eventId);

    public function checkQuotaAvailability(string $ticketTypeId): bool;
}
