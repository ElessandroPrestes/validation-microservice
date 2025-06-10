<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessUserRequest;
use App\Repositories\UserRepository;
use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log; 
use App\Jobs\ProcessUserJob;


/**
 * @OA\Info(
 *     title="User API",
 *     version="1.0.0",
 *     description="API para gerenciar usuários.",
 *     contact={
 *         "email": "contato@seudominio.com"
 *     }
 * )
 */
class UserProcessController extends Controller
{
    private UserRepository $repository;

    /**
     * Construtor da classe
     * 
     * @param UserRepository $repository Repositório de usuários
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

        /**
     * Processa um usuário via serviço externo e armazena os dados.
     * 
     * @OA\Post(
     *     path="/users/process",
     *     summary="Processa um usuário via serviço externo",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"cpf"},
     *             @OA\Property(property="cpf", type="string", pattern="^[0-9]{11}$", example="12345678901")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário processado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="processed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao processar dados externos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="external_api_error"),
     *             @OA\Property(property="message", type="string", example="Erro ao processar dados externos.")
     *         )
     *     )
     * )
     */
    public function process(ProcessUserRequest $request, ExternalApiService $service): JsonResponse
    {
        $cpf = $request->validated()['cpf'];
        $key = "user:cpf:{$cpf}";

        if (Cache::tags(['user'])->has($key)) {
            Log::info('[UserProcess] Cache hit', ['cpf' => $cpf]); 

            return response()->json([
                'status' => 'cached',
                'data' => Cache::tags(['user'])->get($key),
            ]);
        }

        try {
            Log::info('[UserProcess] Iniciando enriquecimento de dados', ['cpf' => $cpf]); 

            $userData = $service->enrichUserData($request->validated());

            $storedUser = $this->repository->store(array_merge($request->validated(), $userData));

            Cache::tags(['user'])->put($key, $storedUser->toArray(), now()->addDay());

            dispatch(new ProcessUserJob($storedUser)); 

            Log::info('[UserProcess] Processamento e job concluídos', ['cpf' => $cpf]); 

            return response()->json([
                'status' => 'processed',
                'data' => $storedUser->toArray(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            Log::error('[UserProcess] Erro externo', [
                'cpf' => $cpf,
                'error' => $e->getMessage()
            ]); 

            return response()->json([
                'status' => 'external_api_error',
                'message' => 'Erro ao processar dados externos.',
            ], 500);
        }
    }
}
