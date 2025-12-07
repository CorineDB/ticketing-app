<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AgentController extends Controller
{
    /**
     * Get all agents
     */
    public function index()
    {
        $agents = User::whereHas('role', function ($query) {
            $query->where('slug', 'agent-de-controle');
        })
            ->select('id', 'name', 'email', 'phone', 'photo', 'availability')
            ->get();

        return response()->json(['data' => $agents]);
    }

    /**
     * Get single agent
     */
    public function show(string $id)
    {
        $agent = User::whereHas('role', function ($query) {
            $query->where('slug', 'agent-de-controle');
        })->findOrFail($id);
        return response()->json(['data' => $agent]);
    }

    /**
     * Create new agent
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'availability' => 'nullable|in:active,inactive,on_break'
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('agents', 'public');
            $validated['photo'] = Storage::url($path);
        }

        // Set default values
        $agentRole = \App\Models\Role::where('slug', 'agent-de-controle')->firstOrFail();
        $validated['role_id'] = $agentRole->id;
        $validated['type'] = 'agent-de-controle'; // Ensure type is set if used elsewhere
        $validated['password'] = Hash::make('password123'); // Default password
        $validated['availability'] = $validated['availability'] ?? 'active';

        $agent = User::create($validated);

        return response()->json($agent, 201);
    }

    /**
     * Update agent status
     */
    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'availability' => 'required|in:active,inactive,on_break'
        ]);

        $agent = User::whereHas('role', function ($query) {
            $query->where('slug', 'agent-de-controle');
        })->findOrFail($id);
        $agent->update($validated);

        return response()->json($agent);
    }

    /**
     * Delete agent
     */
    public function destroy(string $id)
    {
        $agent = User::whereHas('role', function ($query) {
            $query->where('slug', 'agent-de-controle');
        })->findOrFail($id);
        $agent->delete();

        return response()->json(null, 204);
    }
}
