<?php

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Promise\FulfilledPromise;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);


it('salva dados no banco e no cache', function () {
    $clientMock = Mockery::mock(ClientInterface::class);

    $clientMock->shouldReceive('getAsync')
        ->withArgs(fn($url) => str_contains($url, 'viacep.com.br'))
        ->andReturn(new FulfilledPromise(new GuzzleResponse(200, [], json_encode([
            'cep' => '06454000',
            'logradouro' => 'Rua Exemplo',
            'bairro' => 'Centro',
            'localidade' => 'Barueri',
            'uf' => 'SP',
        ]))));

    $clientMock->shouldReceive('getAsync')
        ->withArgs(fn($url) => str_contains($url, 'nationalize.io'))
        ->andReturn(new FulfilledPromise(new GuzzleResponse(200, [], json_encode([
            'name' => 'usuario',
            'country' => [
                ['country_id' => 'BR', 'probability' => 0.95]
            ]
        ]))));

    $clientMock->shouldReceive('getAsync')
        ->withArgs(fn($url) => str_contains($url, 'localhost') && str_contains($url, '/cpf-status'))
        ->andReturn(new FulfilledPromise(new GuzzleResponse(200, [], json_encode([
            'status' => 'valid'
        ]))));

    // Registrar o mock no container
    app()->instance(ClientInterface::class, $clientMock);

    $payload = [
        'cpf' => '12345678900',
        'cep' => '06454000',
        'email' => 'usuario@example.com',
    ];

    $response = $this->postJson('/api/v1/users/process', $payload);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => ['cpf', 'cep', 'email', 'cpf_status']
        ]);

    $this->assertDatabaseHas('user_data', ['cpf' => '12345678900']);

    expect(Cache::tags(['user'])->has('user:cpf:12345678900'))->toBeTrue();
});
