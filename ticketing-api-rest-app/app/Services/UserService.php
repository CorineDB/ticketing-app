<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\Contracts\UserServiceContract;
use App\Services\Core\Eloquent\BaseService;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService implements UserServiceContract
{
    /**
     * Le repository injecté dans le BaseService
     */
    public function __construct(UserRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function createOrganizer(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        $user = $this->repository->create($data);

        $organizerRole = Role::where('slug', 'organizer')->firstOrFail();
        $user->role_id = $organizerRole->id;
        $user->save();

        return $user;
    }

    public function getOrganizers()
    {
        $organizerRole = Role::where('slug', 'organizer')->first();
        if ($organizerRole) {
            return $organizerRole->users()->get();
        }
        return collect();
    }
}
