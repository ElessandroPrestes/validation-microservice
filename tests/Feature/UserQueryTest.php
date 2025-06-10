<?php

namespace Tests\Feature;

use App\Models\UserData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Cache::tags(['user'])->flush();
    UserData::query()->delete(); // Melhor uso para limpar a tabela
});

it('retorna usuário do cache se existir', function () {
    $cpf = '12345678900';

    Cache::tags(['user'])->put("user:cpf:$cpf", [
        'cpf' => $cpf,
        'email' => 'cache@example.com',
    ], 3600);

    getJson("/api/v1/users/{$cpf}")
        ->assertOk()
        ->assertJsonPath('status', 'cached')
        ->assertJsonPath('data.cpf', $cpf)
        ->assertJsonPath('data.email', 'cache@example.com');
});

it('retorna usuário do banco e atualiza o cache se não estiver em cache', function () {
    $user = UserData::factory()->create([
        'cpf' => '12345678900',
        'email' => 'fromdb@example.com',
        'cep' => '12345-678',
        'cep_data' => ['bairro' => 'Centro'],
        'name_origin' => ['source' => 'Sistema Externo'],
        'cpf_status' => 'valid',
    ]);

    getJson("/api/v1/users/{$user->cpf}")
        ->assertOk()
        ->assertJsonPath('status', 'fetched')
        ->assertJsonPath('data.cpf', $user->cpf)
        ->assertJsonPath('data.email', 'fromdb@example.com');

    expect(Cache::tags(['user'])->has("user:cpf:{$user->cpf}"))->toBeTrue();
});

it('retorna 404 se usuário não for encontrado no cache nem no banco', function () {
    getJson('/api/v1/users/00000000000')
        ->assertNotFound()
        ->assertJson(['status' =>'not_found'])
        ->assertJsonPath('message', 'Usuário não encontrado.');
});
