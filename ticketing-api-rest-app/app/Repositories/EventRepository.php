<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryContract;
use App\Repositories\Core\Eloquent\BaseRepository;

class EventRepository extends BaseRepository implements EventRepositoryContract
{
    public function __construct(Event $model)
    {
        parent::__construct($model);
    }

    public function findByOrganisateur(string $organisateurId)
    {
        return $this->model->where('organisateur_id', $organisateurId)->get();
    }

    public function findUpcomingEvents()
    {
        return $this->model
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime', 'asc')
            ->get();
    }

    public function searchEvents(array $filters)
    {
        $query = $this->model->newQuery();

        if (isset($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['q'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['q'] . '%');
            });
        }

        if (isset($filters['date_from'])) {
            $query->where('start_datetime', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('end_datetime', '<=', $filters['date_to']);
        }

        return $query->orderBy('start_datetime', 'asc')->get();
    }
}
