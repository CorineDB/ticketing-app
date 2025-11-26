<?php

namespace DummyNamespace;

use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\Contracts\UserServiceContract;
use DummyInterface;
use App\Services\Core\Eloquent\BaseService;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService implements UserServiceContract
{
    /**
     * Le repository injecté dans le BaseService
     */
    public function __construct(UserRepositoryContract $repository)
    {
        parent::__construct($repository);
    }
}
