<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Repositories\Contracts\TicketRepositoryContract;
use App\Repositories\Core\Eloquent\BaseRepository;
use Illuminate\Support\Str;

class TicketRepository extends BaseRepository implements TicketRepositoryContract
{
    public function __construct(Ticket $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function findByEvent(string $eventId)
    {
        return $this->model->where('event_id', $eventId)->get();
    }

    public function findByMagicLinkToken(string $token)
    {
        return $this->model->where('magic_link_token', $token)->first();
    }

    public function generateUniqueCode(string $eventId): string
    {
        do {
            $code = strtoupper(Str::random(8));
            $exists = $this->model
                ->where('event_id', $eventId)
                ->where('code', $code)
                ->exists();
        } while ($exists);

        return $code;
    }

    public function countByTicketTypeAndStatuses(string $ticketTypeId, array $statuses): int
    {
        return $this->model
            ->where('ticket_type_id', $ticketTypeId)
            ->whereIn('status', $statuses)
            ->count();
    }
}
