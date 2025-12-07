<?php

namespace App\Services;

use App\Repositories\Contracts\EventCounterRepositoryContract;
use App\Repositories\Contracts\EventRepositoryContract;
use App\Services\Contracts\EventServiceContract;
use App\Services\Contracts\TicketTypeServiceContract;
use App\Services\Core\Eloquent\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            Log::info('Event created', [
                'event_id' => $event->id,
                'organisateur_id' => $data['organisateur_id'],
                'data_image_url' => $data['image_url'],
                'event_image_url' => $event->image_url,
            ]);

            $this->counterRepository->createOrGetCounter($event->id);

            foreach ($ticketTypes as $ticketType) {
                $ticketType['event_id'] = $event->id;
                $this->ticketTypeService->create($ticketType);
            }

            return $event->load('ticketTypes');
        });
    }

    /**
     * Create event with ticket types, gates, and media
     */
    public function createWithTicketTypesAndGates(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Extract related data
            $ticketTypes = $data['ticket_types'] ?? [];
            $gates = $data['gates'] ?? [];
            $galleryImages = $data['gallery_images'] ?? [];

            unset($data['ticket_types'], $data['gates'], $data['gallery_images']);

            // Generate slug
            $slug = Str::slug($data['title']);
            $originalSlug = $slug;
            $count = 1;

            while ($this->repository->findBySlugAndOrganisateurId($slug, $data['organisateur_id'])) {
                $slug = $originalSlug . '-' . $count++;
            }
            $data['slug'] = $slug;

            // Handle banner image upload
            if (isset($data['image_url']) && $data['image_url'] instanceof \Illuminate\Http\UploadedFile) {
                $imagePath = $data['image_url']->store('events/banners', 'public');
                $data['image_url'] = Storage::url($imagePath);
            }

            // Handle gallery images upload
            if (!empty($galleryImages)) {
                $galleryUrls = [];
                foreach ($galleryImages as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = $image->store('events/gallery', 'public');
                        $galleryUrls[] = Storage::url($imagePath);
                    }
                }
                $data['gallery_images'] = $galleryUrls;
            }

            // Create event
            $event = $this->repository->create($data);
            $this->counterRepository->createOrGetCounter($event->id);

            // Create ticket types
            $createdTicketTypes = [];
            foreach ($ticketTypes as $ticketType) {
                $ticketType['event_id'] = $event->id;
                $createdType = $this->ticketTypeService->create($ticketType);
                $createdTicketTypes[$ticketType['name']] = $createdType;
            }

            // Attach gates to event with configuration
            foreach ($gates as $gateData) {
                $gateId = $gateData['gate_id'];
                $ticketTypeNames = $gateData['ticket_type_names'] ?? [];

                // Get ticket type IDs
                $ticketTypeIds = [];
                foreach ($ticketTypeNames as $typeName) {
                    if (isset($createdTicketTypes[$typeName])) {
                        $ticketTypeIds[] = $createdTicketTypes[$typeName]->id;
                    }
                }

                // Attach gate to event with configuration
                $event->gates()->attach($gateId, [
                    'id' => Str::uuid(),
                    'agent_id' => $gateData['agent_id'] ?? null,
                    'operational_status' => $gateData['operational_status'] ?? 'active',
                    'schedule' => isset($gateData['schedule']) ? json_encode($gateData['schedule']) : null,
                    'ticket_type_ids' => !empty($ticketTypeIds) ? json_encode($ticketTypeIds) : null,
                    'max_capacity' => $gateData['max_capacity'] ?? null,
                ]);
            }

            return $event->load(['ticketTypes', 'gates']);
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

    public function updateWithTicketTypesAndGates($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            // Extract related data
            $ticketTypes = $data['ticket_types'] ?? [];
            $gates = $data['gates'] ?? [];
            $galleryImages = $data['gallery_images'] ?? [];

            unset($data['ticket_types'], $data['gates'], $data['gallery_images']);

            // Find existing event
            $event = $this->repository->findOrFail($id);

            // Handle banner image upload
            if (isset($data['image_url']) && $data['image_url'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old image if exists
                if ($event->image_url) {
                    $oldPath = str_replace('/storage/', '', $event->image_url);
                    Storage::disk('public')->delete($oldPath);
                }
                $imagePath = $data['image_url']->store('events/banners', 'public');
                $data['image_url'] = Storage::url($imagePath);
            }

            // Handle gallery images upload
            if (!empty($galleryImages)) {
                $galleryUrls = [];
                foreach ($galleryImages as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $imagePath = $image->store('events/gallery', 'public');
                        $galleryUrls[] = Storage::url($imagePath);
                    }
                }
                if (!empty($galleryUrls)) {
                    $data['gallery_images'] = array_merge($event->gallery_images ?? [], $galleryUrls);
                }
            }

            // Update event
            $event = $this->repository->update($event, $data);

            // Update ticket types
            $createdTicketTypes = [];
            $existingTicketTypeIds = [];

            foreach ($ticketTypes as $ticketTypeData) {
                if (isset($ticketTypeData['id'])) {
                    // Update existing ticket type by ID
                    $ticketType = $this->ticketTypeService->find($ticketTypeData['id']);
                    if ($ticketType) {
                        $this->ticketTypeService->update($ticketType->id, $ticketTypeData);
                        $createdTicketTypes[$ticketTypeData['name']] = $ticketType;
                        $existingTicketTypeIds[] = $ticketType->id;
                    }
                } else {
                    // Check if a ticket type with this name already exists for this event
                    $existingType = $event->ticketTypes()
                        ->where('name', $ticketTypeData['name'])
                        ->first();

                    if ($existingType) {
                        // Update the existing ticket type
                        $this->ticketTypeService->update($existingType->id, $ticketTypeData);
                        $createdTicketTypes[$ticketTypeData['name']] = $existingType;
                        $existingTicketTypeIds[] = $existingType->id;
                    } else {
                        // Create new ticket type
                        $ticketTypeData['event_id'] = $event->id;
                        $createdType = $this->ticketTypeService->create($ticketTypeData);
                        $createdTicketTypes[$ticketTypeData['name']] = $createdType;
                        $existingTicketTypeIds[] = $createdType->id;
                    }
                }
            }

            // Delete ticket types that are no longer in the list
            $event->ticketTypes()->whereNotIn('id', $existingTicketTypeIds)->delete();

            // Sync gates
            $gatesToSync = [];
            foreach ($gates as $gateData) {
                $gateId = $gateData['gate_id'];
                $ticketTypeNames = $gateData['ticket_type_names'] ?? [];

                // Get ticket type IDs
                $ticketTypeIds = [];
                foreach ($ticketTypeNames as $typeName) {
                    if (isset($createdTicketTypes[$typeName])) {
                        $ticketTypeIds[] = $createdTicketTypes[$typeName]->id;
                    }
                }

                $gatesToSync[$gateId] = [
                    'id' => Str::uuid(),
                    'agent_id' => $gateData['agent_id'] ?? null,
                    'operational_status' => $gateData['operational_status'] ?? 'active',
                    'schedule' => isset($gateData['schedule']) ? json_encode($gateData['schedule']) : null,
                    'ticket_type_ids' => !empty($ticketTypeIds) ? json_encode($ticketTypeIds) : null,
                    'max_capacity' => $gateData['max_capacity'] ?? null,
                ];
            }

            // Sync gates (this will detach old ones and attach new ones)
            $event->gates()->sync($gatesToSync);

            return $event->load(['ticketTypes', 'gates']);
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
            'tickets_refunded' => $tickets->where('status', 'refunded')->count(),
        ];
    }

    public function publish(string $eventId)
    {
        $event = $this->repository->findOrFail($eventId);

        // Validate that event has at least one ticket type
        $ticketTypesCount = $event->ticketTypes()->count();

        if ($ticketTypesCount === 0) {
            throw new \Exception('Cannot publish event without at least one ticket type. Please add ticket types first.');
        }

        // Update status to published
        $event->status = 'published';
        $event->save();

        return $event->load(['organisateur', 'ticketTypes', 'gates']);
    }
}
