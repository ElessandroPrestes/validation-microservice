<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserData extends Model
{
    use HasFactory;

    protected $fillable = [
        'cpf', 'cep', 'email', 'cep_data', 'name_origin', 'cpf_status',
    ];

    protected $casts = [
        'cep_data' => 'array',
        'name_origin' => 'array',
    ];
}
