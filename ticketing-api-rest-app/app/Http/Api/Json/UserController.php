<?php

namespace DummyNamespace;

use App\Http\Controllers\Controller;
use App\Services\Contracts\UserServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * @var UserServiceContract
     */
    protected $userService;

    /**
     * Injection du service (ou contrat de service)
     */
    public function __construct(UserServiceContract $service)
    {
        $this->userService = $service;
    }

    /**
     * Lister les éléments
     */
    public function index(): JsonResponse
    {
        try {
            $data = $this->userService->list();

            //DummyResourceCollection::collection($items);
            Log::info('DummyClass index success');

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            Log::error('DummyClass index failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load data',
            ], 500);
        }
    }

    /**
     * Créer un nouvel élément
     */
    public function store(DummyRequestClass $request): JsonResponse
    {
        try {
            $payload = $request->validated();
            $item = $this->userService->create($payload);

            Log::info('DummyClass store success');

            return response()->json([
                'success' => true,
                'data'    => $item,
                'message' => 'Created successfully',
            ], 201);
        } catch (\Throwable $e) {
            Log::error('DummyClass store failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Creation failed',
            ], 500);
        }
    }

    /**
     * Voir un élément
     */
    public function show(string $id): JsonResponse
    {
        try {
            $item = $this->userService->find($id);

            if (!$item) {
                Log::warning('DummyClass show not found', ['id' => $id]);

                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                ], 404);
            }

            Log::info('DummyClass show success', ['id' => $id]);

            return response()->json([
                'success' => true,
                'data'    => $item,
            ]);
        } catch (\Throwable $e) {
            Log::error('DummyClass show failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load resource',
            ], 500);
        }
    }

    /**
     * Modifier un élément
     */
    public function update(DummyRequestClass $request, string $id): JsonResponse
    {
        try {
            $payload = $request->validated();
            $item = $this->userService->update($id, $payload);

            Log::info('DummyClass update success', ['id' => $id]);

            return response()->json([
                'success' => true,
                'data'    => $item,
                'message' => 'Updated successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('DummyClass update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Update failed',
            ], 500);
        }
    }

    /**
     * Supprimer un élément
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->userService->delete($id);

            Log::info('DummyClass destroy success', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('DummyClass destroy failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Deletion failed',
            ], 500);
        }
    }
}
