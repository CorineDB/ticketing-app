<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Gates\CreateGateRequest;
use App\Services\Contracts\GateServiceContract;

class GateController extends Controller
{
    protected GateServiceContract $gateService;

    public function __construct(GateServiceContract $gateService)
    {
        $this->gateService = $gateService;
    }

    public function index()
    {
        $gates = $this->gateService->list();
        return response()->json($gates);
    }

    public function store(CreateGateRequest $request)
    {
        $gate = $this->gateService->create($request->validated());
        return response()->json(['data' => $gate], 201);
    }

    public function show(string $id)
    {
        $gate = $this->gateService->get($id);
        return response()->json(['data' => $gate]);
    }

    public function update(CreateGateRequest $request, string $id)
    {
        $gate = $this->gateService->update($id, $request->validated());
        return response()->json(['data' => $gate]);
    }

    public function destroy(string $id)
    {
        $this->gateService->delete($id);
        return response()->json(null, 204);
    }

    /**
     * Assign an agent to a gate for an event
     */
    public function assignAgent(string $eventId, string $gateId)
    {
        $validated = request()->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        // Verify the agent has the correct role
        $agent = \App\Models\User::with('role')->find($validated['agent_id']);

        if (!$agent || $agent->role->slug !== 'agent-de-controle') {
            return response()->json([
                'message' => 'The selected user is not an agent.'
            ], 422);
        }

        // Update the pivot table
        $event = \App\Models\Event::findOrFail($eventId);
        $event->gates()->updateExistingPivot($gateId, [
            'agent_id' => $validated['agent_id']
        ]);

        // Return the updated gate with agent relationship
        $gate = $event->gates()
            ->withPivot([
                'agent_id',
                'operational_status',
                'schedule',
                'ticket_type_ids',
                'max_capacity'
            ])
            ->where('gates.id', $gateId)
            ->first();

        // Load the agent relationship on the pivot
        if ($gate && $gate->pivot && $gate->pivot->agent_id) {
            $gate->pivot->setRelation('agent', \App\Models\User::find($gate->pivot->agent_id));
        }

        return response()->json([
            'message' => 'Agent assigned successfully',
            'data' => $gate
        ]);
    }
}

