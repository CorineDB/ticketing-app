<?php

namespace App\Services;

use App\Repositories\Contracts\EventCounterRepositoryContract;
use App\Repositories\Contracts\EventRepositoryContract;
use App\Services\Contracts\EventServiceContract;
use App\Services\Contracts\TicketTypeServiceContract;
use App\Services\Core\Eloquent\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EventService extends BaseService implements EventServiceContract
{
    protected TicketTypeServiceContract $ticketTypeService;
    protected EventCounterRepositoryContract $counterRepository;

    public function __construct(
        EventRepositoryContract $repository,
        TicketTypeServiceContract $ticketTypeService,
        EventCounterRepositoryContract $counterRepository
    ) {
        parent::__construct($repository);
        $this->ticketTypeService = $ticketTypeService;
        $this->counterRepository = $counterRepository;
    }

    public function createWithTicketTypes(array $data)
    {
        return DB::transaction(function () use ($data) {
            $ticketTypes = $data['ticket_types'] ?? [];
            unset($data['ticket_types']);

            $slug = Str::slug($data['title']);
            $originalSlug = $slug;
            $count = 1;

            while ($this->repository->findBySlugAndOrganisateurId($slug, $data['organisateur_id'])) {
                $slug = $originalSlug . '-' . $count++;
            }
            $data['slug'] = $slug;

            if (isset($data['image_url'])) {
                $imagePath = $data['image_url']->store('events', 'public'); // Store in 'storage/app/public/events'
                $data['image_url'] = Storage::url($imagePath); // Get public URL
            }

            $event = $this->repository->create($data);

            $this->counterRepository->createOrGetCounter($event->id);

            foreach ($ticketTypes as $ticketType) {
                $ticketType['event_id'] = $event->id;
                $this->ticketTypeService->create($ticketType);
            }

            return $event->load('ticketTypes');
        });
    }

    public function updateWithTicketTypes($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $ticketTypes = $data['ticket_types'] ?? null;
            unset($data['ticket_types']);

            $event = $this->repository->findOrFail($id);
            $event = $this->repository->update($event, $data);

            if ($ticketTypes !== null) {
                foreach ($ticketTypes as $ticketTypeData) {
                    if (isset($ticketTypeData['id'])) {
                        $ticketType = $this->ticketTypeService->find($ticketTypeData['id']);
                        $this->ticketTypeService->update($ticketType->id, $ticketTypeData);
                    } else {
                        $ticketTypeData['event_id'] = $event->id;
                        $this->ticketTypeService->create($ticketTypeData);
                    }
                }
            }

            return $event->load('ticketTypes');
        });
    }

    public function getByOrganisateur(string $organisateurId)
    {
        return $this->repository->findByOrganisateur($organisateurId);
    }

    public function getUpcomingEvents()
    {
        return $this->repository->findUpcomingEvents();
    }

    public function search(array $filters)
    {
        return $this->repository->searchEvents($filters);
    }

    public function getEventBySlugAndOrganisateurId(string $slug, string $organisateurId)
    {
        return $this->repository->findBySlugAndOrganisateurId($slug, $organisateurId);
    }

    public function getEventBySlug(string $slug)
    {
        return $this->repository->findBySlug($slug);
    }

    public function getEventStats(string $eventId)
    {
        $event = $this->repository->findOrFail($eventId);
        $counter = $this->counterRepository->find($eventId);

        $tickets = $event->tickets;

        return [
            'total_tickets' => $tickets->count(),
            'total_paid' => $tickets->where('status', 'paid')->count(),
            'current_in' => $counter ? $counter->current_in : 0,
            'capacity' => $event->capacity,
            'tickets_issued' => $tickets->where('status', 'issued')->count(),
            'tickets_reserved' => $tickets->where('status', 'reserved')->count(),
            'tickets_in' => $tickets->where('status', 'in')->count(),
            'tickets_out' => $tickets->where('status', 'out')->count(),
            'tickets_invalid' => $tickets->where('status', 'invalid')->count(),
            'tickets_refunded' => $tickets->where('status', 'refunded')->count(),
        ];
    }
}
