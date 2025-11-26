<?php

namespace App\Repositories;

use App\Models\TicketScanLog;
use App\Repositories\Contracts\TicketScanLogRepositoryContract;
use App\Repositories\Core\Eloquent\BaseRepository;

class TicketScanLogRepository extends BaseRepository implements TicketScanLogRepositoryContract
{
    public function __construct(TicketScanLog $model)
    {
        parent::__construct($model);
    }

    public function findByTicket(string $ticketId)
    {
        return $this->model
            ->where('ticket_id', $ticketId)
            ->orderBy('scan_time', 'desc')
            ->get();
    }

    public function findByGate(string $gateId)
    {
        return $this->model
            ->where('gate_id', $gateId)
            ->orderBy('scan_time', 'desc')
            ->get();
    }

    public function findByAgent(string $agentId)
    {
        return $this->model
            ->where('agent_id', $agentId)
            ->orderBy('scan_time', 'desc')
            ->get();
    }
}
