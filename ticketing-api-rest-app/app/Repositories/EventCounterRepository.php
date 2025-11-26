<?php

namespace App\Repositories;

use App\Models\EventCounter;
use App\Repositories\Contracts\EventCounterRepositoryContract;
use App\Repositories\Core\Eloquent\BaseRepository;
use Illuminate\Support\Facades\DB;

class EventCounterRepository extends BaseRepository implements EventCounterRepositoryContract
{
    public function __construct(EventCounter $model)
    {
        parent::__construct($model);
    }

    public function incrementCurrentIn(string $eventId): void
    {
        DB::transaction(function () use ($eventId) {
            $this->model
                ->where('event_id', $eventId)
                ->lockForUpdate()
                ->increment('current_in');

            $this->model
                ->where('event_id', $eventId)
                ->update(['updated_at' => now()]);
        });
    }

    public function decrementCurrentIn(string $eventId): void
    {
        DB::transaction(function () use ($eventId) {
            $this->model
                ->where('event_id', $eventId)
                ->lockForUpdate()
                ->where('current_in', '>', 0)
                ->decrement('current_in');

            $this->model
                ->where('event_id', $eventId)
                ->update(['updated_at' => now()]);
        });
    }

    public function getCurrentIn(string $eventId): int
    {
        $counter = $this->model->find($eventId);
        return $counter ? $counter->current_in : 0;
    }

    public function createOrGetCounter(string $eventId)
    {
        return $this->model->firstOrCreate(
            ['event_id' => $eventId],
            ['current_in' => 0]
        );
    }
}
