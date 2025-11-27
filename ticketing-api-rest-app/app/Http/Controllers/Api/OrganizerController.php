<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrganizerRequest; // Import StoreOrganizerRequest
use App\Services\Contracts\UserServiceContract; // Import UserServiceContract
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
        // Will be implemented next
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrganizerRequest $request) // Use StoreOrganizerRequest for validation
    {
        // Authorization is handled in StoreOrganizerRequest
        $organizer = $this->userService->createOrganizer($request->validated());
        return response()->json($organizer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
