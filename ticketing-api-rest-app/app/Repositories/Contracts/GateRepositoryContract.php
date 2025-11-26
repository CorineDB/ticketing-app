<?php

namespace App\Repositories\Contracts;

use App\Repositories\Core\Contracts\BaseRepositoryInterface;

interface GateRepositoryContract extends BaseRepositoryInterface
{
    public function findActiveGates();

    public function findByType(string $type);
}
