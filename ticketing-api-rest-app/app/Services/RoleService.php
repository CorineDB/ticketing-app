<?php

namespace App\Services;

use App\Repositories\Contracts\RoleRepositoryContract;
use App\Services\Contracts\RoleServiceContract;
use App\Services\Core\Eloquent\BaseService;

class RoleService extends BaseService implements RoleServiceContract
{
    public function __construct(RoleRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function getBySlug(string $slug)
    {
        return $this->repository->findBySlug($slug);
    }
}
