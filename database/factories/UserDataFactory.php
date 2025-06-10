<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserData>
 */
class UserDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cpf' => $this->faker->unique()->numerify('###########'),
            'email' => $this->faker->unique()->safeEmail,
            'cep' => $this->faker->postcode,
            'cep_data' => json_encode(['bairro' => $this->faker->word]),
            'name_origin' => ['source' => $this->faker->company],
            'cpf_status' => 'valid',
        ];
    }
}
