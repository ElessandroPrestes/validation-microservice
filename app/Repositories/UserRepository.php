<?php

namespace App\Repositories;

use App\Models\UserData;

class UserRepository
{
    public function store(array $data): UserData
    {
        return UserData::updateOrCreate(
            ['cpf' => $data['cpf']],
            [
                'cep' => $data['cep'],
                'email' => $data['email'],
                'cep_data' => $data['cep_data'],
                'name_origin' => $data['name_origin'],
                'cpf_status' => $data['cpf_status'],
            ]
        );
    }

    public function findByCpf(string $cpf): ?UserData
    {
        return UserData::where('cpf', $cpf)->first();
    }
}
