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
        return response()->json(['data' => $gates]);
    }

    public function store(CreateGateRequest $request)
    {
        $gate = $this->gateService->create($request->validated());
        return response()->json($gate, 201);
    }

    public function show(string $id)
    {
        $gate = $this->gateService->get($id);
        return response()->json($gate);
    }

    public function update(CreateGateRequest $request, string $id)
    {
        $gate = $this->gateService->update($id, $request->validated());
        return response()->json($gate);
    }

    public function destroy(string $id)
    {
        $this->gateService->delete($id);
        return response()->json(null, 204);
    }
}
