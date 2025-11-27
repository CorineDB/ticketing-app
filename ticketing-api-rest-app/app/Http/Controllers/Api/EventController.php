<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Events\CreateEventRequest;
use App\Services\Contracts\EventServiceContract;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected EventServiceContract $eventService;

    public function __construct(EventServiceContract $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'date_from', 'date_to']);
        $events = $this->eventService->search($filters);
        return response()->json(['data' => $events->load(["organisateur", "ticketTypes", "counter"])]);
    }

    public function store(CreateEventRequest $request)
    {
        $data = $request->validated();
        $data['organisateur_id'] = auth()->id();
        $data['created_by'] = auth()->id();

        $event = $this->eventService->createWithTicketTypes($data);
        return response()->json($event, 201);
    }

    public function show(string $id)
    {
        $event = $this->eventService->get($id);
        return response()->json($event->load(["organisateur", "ticketTypes", "tickets", "counter"]));
    }

    public function showBySlug(string $slug)
    {
        $organisateurId = auth()->id(); // Assuming authenticated user is the organisateur
        $event = $this->eventService->getEventBySlugAndOrganisateurId($slug, $organisateurId);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        return response()->json($event->load(["organisateur", "ticketTypes", "tickets", "counter"]));
    }

    public function update(CreateEventRequest $request, string $id)
    {
        $event = $this->eventService->updateWithTicketTypes($id, $request->validated());
        return response()->json($event);
    }

    public function destroy(string $id)
    {
        $this->eventService->delete($id);
        return response()->json(null, 204);
    }

    public function stats(string $id)
    {
        $stats = $this->eventService->getEventStats($id);
        return response()->json($stats);
    }
}
