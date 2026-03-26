<?php

namespace Database\Factories;

use App\Models\Kid;
use Illuminate\Database\Eloquent\Factories\Factory;

class KidFactory extends Factory
{
    protected $model = Kid::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'birth_date' => $this->faker->dateTimeBetween('2019-01-01', '2023-12-31')->format('d/m/Y'),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'ethnicity' => $this->faker->randomElement(['branco', 'pardo', 'negro', 'indigena', 'amarelo', 'nao_declarado']),
        ];
    }
}
