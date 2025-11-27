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

    public function find(mixed $id): ?Model
    {
        return $this->repository->find($id);
    }

    public function findOrFail(mixed $id): Model
    {
        return $this->repository->findOrFail($id);
    }

    public function all(array $columns = ['*']): iterable
    {
        return $this->repository->all($columns);
    }

    public function paginate(int $limit = 15, array $columns = ['*'])
    {
        return $this->repository->paginate($limit, $columns);
    }

    public function list(int $limit = 15)
    {
        return $this->paginate($limit);
    }

    public function get($id): Model
    {
        return $this->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return DB::transaction(fn () =>
            $this->repository->create($data)
        );
    }

    public function update(Model|string $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            // Si $model est un ID, on récupère le modèle
            if (is_string($model)) {
                $model = $this->repository->findOrFail($model);
            }
            return $this->repository->update($model, $data);
        });
    }

    public function delete(Model|string $model): bool
    {
        // Si $model est un ID, on récupère le modèle
        if (is_string($model)) {
            $model = $this->repository->findOrFail($model);
        }
        return $this->repository->delete($model);
    }

    public function query()
    {
        return $this->repository->query();
    }
}
