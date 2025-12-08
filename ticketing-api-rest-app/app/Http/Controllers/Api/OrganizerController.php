<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrganizerRequest;
use App\Services\Contracts\UserServiceContract;
use Illuminate\Http\Request;

class OrganizerController extends Controller
{
    protected UserServiceContract $userService;

    public function __construct(UserServiceContract $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all users with organizer role
        $organizers = \App\Models\User::withoutGlobalScopes()
            ->with(['role'])
            ->whereHas('role', function ($query) {
                $query->where('slug', 'organizer');
            })
            ->get();

        return response()->json(['data' => $organizers]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrganizerRequest $request)
    {
        $organizer = $this->userService->createOrganizer($request->validated());
        return response()->json($organizer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organizer = \App\Models\User::withoutGlobalScopes()
            ->with(['role'])
            ->whereHas('role', function ($query) {
                $query->where('slug', 'organizer');
            })
            ->find($id);

        if (!$organizer) {
            return response()->json(['message' => 'Organizer not found'], 404);
        }

        return response()->json(['data' => $organizer]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $organizer = \App\Models\User::withoutGlobalScopes()
            ->with(['role'])
            ->whereHas('role', function ($query) {
                $query->where('slug', 'organizer');
            })
            ->find($id);

        if (!$organizer) {
            return response()->json(['message' => 'Organizer not found'], 404);
        }

        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
        ]);

        // Remove password if it's empty or null
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            // Hash password if provided
            $validated['password'] = bcrypt($validated['password']);
        }

        $updated = $this->userService->update($organizer, $validated);
        return response()->json(['data' => $updated]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $organizer = \App\Models\User::withoutGlobalScopes()
            ->with(['role'])
            ->whereHas('role', function ($query) {
                $query->where('slug', 'organizer');
            })
            ->find($id);

        if (!$organizer) {
            return response()->json(['message' => 'Organizer not found'], 404);
        }

        $this->userService->delete($organizer);
        return response()->json(null, 204);
    }
}
