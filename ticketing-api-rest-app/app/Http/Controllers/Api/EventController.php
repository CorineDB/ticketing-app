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

        // For public routes, only show published events
        $filters['published_only'] = true;

        $events = $this->eventService->search($filters);
        return response()->json(['data' => $events->load(["organisateur", "ticketTypes", "counter"])]);
    }

    public function store(CreateEventRequest $request)
    {
        $data = $request->validated();
        $data['organisateur_id'] = auth()->id();
        $data['created_by'] = auth()->id();

        // Use new method that handles gates and gallery
        $event = $this->eventService->createWithTicketTypesAndGates($data);
        return response()->json($event, 201);
    }

    public function show(string $id)
    {
        $event = $this->eventService->get($id);
        return response()->json($event->load(["organisateur", "ticketTypes", "tickets", "counter", "gates"]));
    }

    public function showBySlug(string $slug)
    {
        // Public route - no authentication required
        $event = $this->eventService->getEventBySlug($slug);

        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        return response()->json($event->load(["organisateur", "ticketTypes", "tickets", "counter", "gates"]));
    }

    public function update(CreateEventRequest $request, string $id)
    {
        // Get the event to check ownership
        $event = $this->eventService->get($id);
        
        // Check if user is the owner or super-admin
        $user = auth()->user();
        if ($event->organisateur_id !== $user->id && !$user->isSuperAdmin()) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à modifier cet événement.'
            ], 403);
        }
        
        $event = $this->eventService->updateWithTicketTypesAndGates($id, $request->validated());
        return response()->json($event);
    }

    public function destroy(string $id)
    {
        $this->eventService->delete($id);
        return response()->json(null, 204);
    }

    public function publish(string $id)
    {
        try {
            $event = $this->eventService->publish($id);
            return response()->json([
                'message' => 'Event published successfully',
                'event' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function stats(string $id)
    {
        $stats = $this->eventService->getEventStats($id);
        return response()->json($stats);
    }
}
