<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\UserServiceContract;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserServiceContract $userService;

    public function __construct(UserServiceContract $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!auth()->check() || (!$user->isSuperAdmin() && !$user->isOrganizer())) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Get pagination parameters
        $perPage = $request->get('per_page', 15);
        $page = $request->get('page', 1);

        // Get all users with their roles and permissions
        // Filtering is automatically handled by OrganizerScope in the User model
        $users = \App\Models\User::with(['role.permissions'])->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'type' => 'nullable|string',
        ], [
            'email.unique' => 'This email address is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'role_id.exists' => 'The selected role does not exist.',
        ]);

        // If the current user is an organizer, automatically link the new user to their organization
        if ($currentUser->isOrganizer()) {
            $validated['organisateur_id'] = $currentUser->id;

            // Prevent organizers from creating super-admins or other organizers
            $role = \App\Models\Role::find($validated['role_id']);
            if ($role && in_array($role->slug, ['super-admin', 'organizer'])) {
                return response()->json([
                    'message' => 'You are not authorized to create users with this role.'
                ], 403);
            }
        }

        $user = $this->userService->create($validated);
        $user->load(['role.permissions']);

        return response()->json(['data' => $user], 201);
    }

    public function show(string $id)
    {
        $user = \App\Models\User::with(['role.permissions'])->findOrFail($id);
        return response()->json(['data' => $user]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'password' => 'sometimes|nullable|string|min:8',
            'role_id' => 'sometimes|required|exists:roles,id',
            'type' => 'nullable|string',
        ], [
            'email.unique' => 'This email address is already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'role_id.exists' => 'The selected role does not exist.',
        ]);

        $user = $this->userService->update($id, $validated);
        $user->load(['role.permissions']);

        return response()->json(['data' => $user]);
    }

    public function destroy(string $id)
    {
        $this->userService->delete($id);
        return response()->json(null, 204);
    }
}
