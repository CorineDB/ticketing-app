<?php

namespace App\Repositories\Contracts;

use App\Repositories\Core\Contracts\BaseRepositoryInterface;

interface EventCounterRepositoryContract extends BaseRepositoryInterface
{
    public function incrementCurrentIn(string $eventId): void;

    public function decrementCurrentIn(string $eventId): void;

    public function getCurrentIn(string $eventId): int;

    public function createOrGetCounter(string $eventId);
}
