<?php

namespace App\Repositories\Core\Eloquent;

use App\Repositories\Core\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find($id): ?Model
    {
        return $this->model->find($id);
    }

    public function findOrFail($id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function all(array $columns = ['*'], array $relations = []): iterable
    {
        $query = $this->model->newQuery();

        // Apply forAuthUser scope only if it exists
        if (method_exists($this->model, 'scopeForAuthUser')) {
            $query = $query->forAuthUser();
        }

        return $query->with($relations)->orderBy("created_at", "desc")->get($columns);
    }

    public function paginate(int $limit = 15, array $columns = ['*'], array $relations = [])
    {
        $query = $this->model->newQuery();

        // Apply forAuthUser scope only if it exists
        if (method_exists($this->model, 'scopeForAuthUser')) {
            $query = $query->forAuthUser();
        }

        return $query->with($relations)->orderBy("created_at", "desc")->paginate($limit, $columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model;
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function query()
    {
        return $this->model->newQuery();
    }
}
