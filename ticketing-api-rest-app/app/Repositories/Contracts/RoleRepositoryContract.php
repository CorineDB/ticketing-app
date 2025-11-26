<?php

namespace App\Repositories\Contracts;

use App\Repositories\Core\Contracts\BaseRepositoryInterface;

interface RoleRepositoryContract extends BaseRepositoryInterface
{
    public function findBySlug(string $slug);
}
