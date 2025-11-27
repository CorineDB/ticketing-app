<?php

namespace App\Services;

use App\Repositories\Contracts\TicketTypeRepositoryContract;
use App\Services\Contracts\TicketTypeServiceContract;
use App\Services\Core\Eloquent\BaseService;
use Illuminate\Database\Eloquent\Model;

class TicketTypeService extends BaseService implements TicketTypeServiceContract
{
    public function __construct(TicketTypeRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        return $this->repository->create($data);
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
