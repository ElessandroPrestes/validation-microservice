<?php

use App\Http\Controllers\Api\MockController;
use App\Http\Controllers\Api\UserProcessController;
use App\Http\Controllers\Api\UserQueryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/v1/users/process', [UserProcessController::class, 'process'])
    ->name('api.v1.users.process');

Route::get('/v1/mock/cpf-status/{cpf}', [MockController::class, 'cpfStatus'])
    ->name('api.v1.mock.cpf-status');

Route::get('/v1/users/{cpf}', [UserQueryController::class, 'show'])
    ->name('api.v1.users.show');
