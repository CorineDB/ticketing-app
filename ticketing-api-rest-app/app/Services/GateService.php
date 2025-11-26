<?php

namespace App\Services;

use App\Repositories\Contracts\GateRepositoryContract;
use App\Services\Contracts\GateServiceContract;
use App\Services\Core\Eloquent\BaseService;

class GateService extends BaseService implements GateServiceContract
{
    public function __construct(GateRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function getActiveGates()
    {
        return $this->repository->findActiveGates();
    }

    public function getByType(string $type)
    {
        return $this->repository->findByType($type);
    }
}
