<?php

namespace App\Services;

use App\Repositories\Contracts\TicketScanLogRepositoryContract;
use App\Services\Contracts\TicketScanLogServiceContract;
use App\Services\Core\Eloquent\BaseService;

class TicketScanLogService extends BaseService implements TicketScanLogServiceContract
{
    public function __construct(TicketScanLogRepositoryContract $repository)
    {
        parent::__construct($repository);
    }

    public function getByTicket(string $ticketId)
    {
        return $this->repository->findByTicket($ticketId);
    }

    public function getByGate(string $gateId)
    {
        return $this->repository->findByGate($gateId);
    }

    public function getByAgent(string $agentId)
    {
        return $this->repository->findByAgent($agentId);
    }

    public function logScan(array $data)
    {
        return $this->repository->create($data);
    }
}
