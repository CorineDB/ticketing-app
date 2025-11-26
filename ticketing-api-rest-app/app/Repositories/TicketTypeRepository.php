<?php

namespace App\Repositories;

use App\Models\TicketType;
use App\Repositories\Contracts\TicketTypeRepositoryContract;
use App\Repositories\Core\Eloquent\BaseRepository;

class TicketTypeRepository extends BaseRepository implements TicketTypeRepositoryContract
{
    public function __construct(TicketType $model)
    {
        parent::__construct($model);
    }

    public function findByEvent(string $eventId)
    {
        return $this->model->where('event_id', $eventId)->get();
    }

    public function checkQuotaAvailability(string $ticketTypeId): bool
    {
        $ticketType = $this->findOrFail($ticketTypeId);

        if ($ticketType->quota === null) {
            return true;
        }

        $ticketsCount = $ticketType->tickets()->count();

        return $ticketsCount < $ticketType->quota;
    }
}
