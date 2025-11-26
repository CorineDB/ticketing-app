<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\TicketTypeServiceContract;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    protected TicketTypeServiceContract $ticketTypeService;

    public function __construct(TicketTypeServiceContract $ticketTypeService)
    {
        $this->ticketTypeService = $ticketTypeService;
    }

    public function index(string $eventId)
    {
        $ticketTypes = $this->ticketTypeService->getByEvent($eventId);
        return response()->json(['data' => $ticketTypes]);
    }

    public function store(Request $request, string $eventId)
    {
        $data = $request->all();
        $data['event_id'] = $eventId;
        $ticketType = $this->ticketTypeService->create($data);
        return response()->json($ticketType, 201);
    }

    public function show(string $id)
    {
        $ticketType = $this->ticketTypeService->get($id);
        return response()->json($ticketType);
    }

    public function update(Request $request, string $id)
    {
        $ticketType = $this->ticketTypeService->update($id, $request->all());
        return response()->json($ticketType);
    }

    public function destroy(string $id)
    {
        $this->ticketTypeService->delete($id);
        return response()->json(null, 204);
    }
}
