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
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Get all users with optional filters
        $users = $this->userService->all(['*'], ['role', 'organisateur']);

        // Apply filters if provided
        $search = $request->query('search');
        $roleId = $request->query('role_id');
        $status = $request->query('status');

        if ($search || $roleId || $status) {
            $users = $users->filter(function ($user) use ($search, $roleId, $status) {
                if ($search && !str_contains(strtolower($user->name . ' ' . $user->email), strtolower($search))) {
                    return false;
                }
                if ($roleId && (!$user->role || $user->role->id !== $roleId)) {
                    return false;
                }
                if ($status && ($user->status ?? 'active') !== $status) {
                    return false;
                }
                return true;
            })->values();
        }

        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $user = $this->userService->create($request->all());
        return response()->json($user, 201);
    }

    public function show(string $id)
    {
        $user = $this->userService->get($id);
        return response()->json($user);
    }

    public function update(Request $request, string $id)
    {
        $user = $this->userService->update($id, $request->all());
        return response()->json($user);
    }

    public function destroy(string $id)
    {
        $this->userService->delete($id);
        return response()->json(null, 204);
    }
}
