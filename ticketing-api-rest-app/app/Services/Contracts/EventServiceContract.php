<?php

namespace App\Services\Contracts;

use App\Services\Core\Contracts\BaseServiceInterface;

interface EventServiceContract extends BaseServiceInterface
{
    public function createWithTicketTypes(array $data);

    public function updateWithTicketTypes($id, array $data);

    public function getByOrganisateur(string $organisateurId);

    public function getUpcomingEvents();

    public function search(array $filters);

    public function getEventStats(string $eventId);
}
