<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('retorna erro de validação se os dados forem inválidos', function () {
    $response = $this->postJson('/api/v1/users/process', []);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['cpf', 'cep', 'email']);
});

it('retorna resposta em cache se existir', function () {

    Cache::tags(['user'])->put('user:cpf:12345678900', ['test' => 'cached'], 3600);

    $response = $this->postJson('/api/v1/users/process', [
        'cpf' => '12345678900',
        'cep' => '06454000',
        'email' => 'usuario@example.com',
    ]);

    $response->assertStatus(200)
             ->assertJson([
                 'status' => 'cached',
                 'data' => ['test' => 'cached'],
             ]);
});
