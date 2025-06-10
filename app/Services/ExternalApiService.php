<?php

namespace App\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;

class ExternalApiService
{
    protected ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function enrichUserData(array $data): array
    {
        $firstName = explode('@', $data['email'])[0];

        $promises = [
            'viacep' => $this->withRetry(fn() => $this->client->getAsync("https://viacep.com.br/ws/{$data['cep']}/json")),
            'nationalize' => $this->withRetry(fn() => $this->client->getAsync("https://api.nationalize.io?name={$firstName}")),
            'cpf_status' => $this->withRetry(fn() => $this->client->getAsync("http://nginx/api/v1/mock/cpf-status/{$data['cpf']}")),
        ];

        $results = Promise\Utils::unwrap($promises);

        return [
            'cep_data' => json_decode($results['viacep']->getBody()->getContents(), true),
            'name_origin' => json_decode($results['nationalize']->getBody()->getContents(), true),
            'cpf_status' => json_decode($results['cpf_status']->getBody()->getContents(), true)['status'] ?? 'unknown',
        ];
    }

    private function withRetry(callable $fn, int $attempts = 3)
    {
        return Promise\Create::promiseFor(
            retry($attempts, function () use ($fn) {
                try {
                    return $fn();
                } catch (\Exception $e) {
                    Log::warning("Tentativa de API falhou: " . $e->getMessage());
                    throw $e;
                }
            })
        );
    }
}
