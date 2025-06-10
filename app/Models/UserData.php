<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    protected $fillable = [
        'cpf', 'cep', 'email', 'cep_data', 'name_origin', 'cpf_status',
    ];

    protected $casts = [
        'cep_data' => 'array',
        'name_origin' => 'array',
    ];
}
