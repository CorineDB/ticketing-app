<?php

namespace App\Services\Contracts;

use App\Services\Core\Contracts\BaseServiceInterface;

interface RoleServiceContract extends BaseServiceInterface
{
    public function getBySlug(string $slug);
}
