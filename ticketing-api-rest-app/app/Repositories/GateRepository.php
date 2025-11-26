<?php

namespace App\Repositories;

use App\Models\Gate;
use App\Repositories\Contracts\GateRepositoryContract;
use App\Repositories\Core\Eloquent\BaseRepository;

class GateRepository extends BaseRepository implements GateRepositoryContract
{
    public function __construct(Gate $model)
    {
        parent::__construct($model);
    }

    public function findActiveGates()
    {
        return $this->model->where('status', 'active')->get();
    }

    public function findByType(string $type)
    {
        return $this->model->where('gate_type', $type)->get();
    }
}
