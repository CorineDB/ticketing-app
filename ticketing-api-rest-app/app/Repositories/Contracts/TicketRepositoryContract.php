<?php

namespace App\Repositories\Contracts;

use App\Repositories\Core\Contracts\BaseRepositoryInterface;

interface TicketRepositoryContract extends BaseRepositoryInterface
{
    public function findByCode(string $code);

    public function findByEvent(string $eventId);

    public function findByMagicLinkToken(string $token);

    public function generateUniqueCode(string $eventId): string;
}
