<?php

namespace Database\Factories;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class KidFactory extends Factory
{
    protected $model = Kid::class;

    public function definition()
    {
        // Gera uma data de nascimento aleatória entre 01/01/2019 e 31/12/2021
        $birthDate = $this->faker->dateTimeBetween('2018-01-01', '2023-12-31')->format('d/m/Y');

        return [
            'name' => $this->faker->name,
            'birth_date' => $birthDate,
            'gender' => $this->faker->randomElement(['M', 'F']),
            'ethnicity' => $this->faker->randomElement(array_keys(Kid::ETHNICITIES)),
            'created_by' => 1,
        ];
    }
}
