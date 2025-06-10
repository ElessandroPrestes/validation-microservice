<?php

namespace App\Http\Controllers\Api;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Consulta de usuários"
 * )
 */
class UserQueryController
{
    /**
     * Consulta um usuário por CPF.
     * 
     * @OA\Get(
     *     path="/users/{cpf}",
     *     summary="Consulta um usuário por CPF",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="cpf",
     *         in="path",
     *         required=true,
     *         description="CPF do usuário a ser consultado",
     *         @OA\Schema(type="string", pattern="^[0-9]{11}$", example="12345678901")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="fetched"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="not_found"),
     *             @OA\Property(property="message", type="string", example="Usuário não encontrado.")
     *         )
     *     )
     * )
     */
    public function show(string $cpf, UserRepository $repository): JsonResponse
    {
        $key = "user:cpf:$cpf";

        Log::info('[UserQuery] Requisição recebida', compact('cpf'));

        if (Cache::tags(['user'])->has($key)) {
            Log::info('[UserQuery] Cache encontrado', compact('cpf'));

            return response()->json([
                'status' => 'cached',
                'data' => Cache::tags(['user'])->get($key),
            ]);
        }

        $user = $repository->findByCpf($cpf);

        if (!$user) {
            Log::warning('[UserQuery] Usuário não encontrado', [
                'cpf' => $cpf,
                'raw_result' => $user
            ]);

            return response()->json([
                'status' => 'not_found',
                'message' => 'Usuário não encontrado.'
            ], 404);
        }

        Cache::tags(['user'])->put($key, $user->toArray(), now()->addDay());

        Log::info('[UserQuery] Usuário retornado do banco e cacheado', compact('cpf'));

        return response()->json([
            'status' => 'fetched',
            'data' => $user->toArray(),
        ]);
    }
}
