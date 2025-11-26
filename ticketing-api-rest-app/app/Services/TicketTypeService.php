<?php

namespace App\Services;

use App\Repositories\Contracts\TicketTypeRepositoryContract;
use App\Services\Contracts\TicketTypeServiceContract;
use App\Services\Core\Eloquent\BaseService;

class TicketTypeService extends BaseService implements TicketTypeServiceContract
{
    public function __construct(TicketTypeRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function getByEvent(string $eventId)
    {
        return $this->repository->findByEvent($eventId);
    }

    public function checkQuotaAvailability(string $ticketTypeId): bool
    {
        return $this->repository->checkQuotaAvailability($ticketTypeId);
    }
}
