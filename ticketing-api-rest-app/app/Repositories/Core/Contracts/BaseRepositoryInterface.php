<?php

namespace App\Repositories\Core\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function find(mixed $id): ?Model;

    public function findOrFail(mixed $id): Model;

    public function all(array $columns = ['*'], array $relations = []): iterable;

    public function paginate(int $limit = 15, array $columns = ['*'], array $relations = []);

    public function create(array $data): Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;

    public function query();
}
