<?php

namespace App\Services\Contracts;

use App\Services\Core\Contracts\BaseServiceInterface;

interface GateServiceContract extends BaseServiceInterface
{
    public function getActiveGates();

    public function getByType(string $type);
}
