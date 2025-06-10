<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class MockController
{
    public function cpfStatus(string $cpf): JsonResponse
    {
        $status = Arr::random(['limpo', 'pendente', 'negativado']);
        return response()->json(['cpf' => $cpf, 'status' => $status]);
    }
}
