<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\Core\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Log;

class UserRepository extends BaseRepository implements UserRepositoryContract
{
    /**
     * Le repository injecté dans le BaseRepository
     */
    public function __construct(User $user)
    {
        parent::__construct($user);
    }
}
