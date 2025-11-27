<?php

namespace App\Repositories\Contracts;

use App\Repositories\Core\Contracts\BaseRepositoryInterface;

interface EventRepositoryContract extends BaseRepositoryInterface
{
    public function findByOrganisateur(string $organisateurId);

    public function findUpcomingEvents();

    public function searchEvents(array $filters);

    public function findBySlugAndOrganisateurId(string $slug, string $organisateurId);
}
