<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\RoleServiceContract;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected RoleServiceContract $roleService;

    public function __construct(RoleServiceContract $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        // Filtering is automatically handled by RoleOwnershipScope in the Role model
        $roles = \App\Models\Role::all();
        return response()->json(['data' => $roles]);
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ], [
            'name.unique' => 'A role with this name already exists.',
            'slug.unique' => 'A role with this slug already exists.',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Automatically set created_by to current user
        $validated['created_by'] = $currentUser->id;

        // Prevent organizers from creating super-admin or organizer roles
        if ($currentUser->isOrganizer()) {
            $restrictedSlugs = ['super-admin', 'organizer'];
            if (in_array($validated['slug'], $restrictedSlugs)) {
                return response()->json([
                    'message' => 'You are not authorized to create this type of role.'
                ], 403);
            }
        }

        $role = $this->roleService->create($validated);

        // Attach permissions if provided
        if (!empty($validated['permission_ids'])) {
            $role->permissions()->sync($validated['permission_ids']);
            $role->load('permissions');
        }

        return response()->json(['data' => $role], 201);
    }

    public function show(string $id)
    {
        $role = $this->roleService->get($id);
        return response()->json($role);
    }

    public function update(Request $request, string $id)
    {
        $role = $this->roleService->update($id, $request->all());
        return response()->json($role);
    }

    public function destroy(string $id)
    {
        $this->roleService->delete($id);
        return response()->json(null, 204);
    }
}
