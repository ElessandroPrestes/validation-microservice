<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessUserRequest;
use App\Repositories\UserRepository;
use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class UserProcessController extends Controller
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function process(ProcessUserRequest $request, ExternalApiService $service): JsonResponse
    {
        $cpf = $request->validated()['cpf'];
        $key = "user:cpf:{$cpf}";

        if (Cache::tags(['user'])->has($key)) {
            return response()->json([
                'status' => 'cached',
                'data' => Cache::tags(['user'])->get($key),
            ]);
        }

        try {
    
            $userData = $service->enrichUserData($request->validated());

            $storedUser = $this->repository->store(array_merge($request->validated(), $userData));

            Cache::tags(['user'])->put($key, $storedUser->toArray(), now()->addDay());

            return response()->json([
                'status' => 'processed',
                'data' => $storedUser->toArray(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'status' => 'external_api_error',
                'message' => 'Erro ao processar dados externos.',
            ], 500);
        }
    }
}
