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

        // Get all users - NO relations to avoid errors
        $users = $this->userService->all();

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
