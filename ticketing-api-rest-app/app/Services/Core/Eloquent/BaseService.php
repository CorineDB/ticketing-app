<?php

namespace App\Services\Core\Eloquent;

use App\Services\Core\Contracts\BaseServiceInterface;
use App\Repositories\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseService implements BaseServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(BaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list(int $limit = 15)
    {
        return $this->repository->paginate($limit);
    }

    public function get($id): Model
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return DB::transaction(fn () =>
            $this->repository->create($data)
        );
    }

    public function update($id, array $data): Model
    {
        return DB::transaction(function () use ($id, $data) {
            $model = $this->repository->findOrFail($id);
            return $this->repository->update($model, $data);
        });
    }

    public function delete($id): bool
    {
        $model = $this->repository->findOrFail($id);
        return $this->repository->delete($model);
    }
}
