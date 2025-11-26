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
        $roles = $this->roleService->list();
        return response()->json(['data' => $roles]);
    }

    public function store(Request $request)
    {
        $role = $this->roleService->create($request->all());
        return response()->json($role, 201);
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
