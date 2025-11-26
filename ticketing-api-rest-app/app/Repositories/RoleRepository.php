<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryContract;
use App\Repositories\Core\Eloquent\BaseRepository;

class RoleRepository extends BaseRepository implements RoleRepositoryContract
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(string $slug)
    {
        return $this->model->where('slug', $slug)->first();
    }
}
