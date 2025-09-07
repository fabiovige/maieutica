<?php

namespace Database\Factories;

use App\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialtyFactory extends Factory
{
    protected $model = Specialty::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Psicologia Clínica',
                'Psicopedagogia',
                'Neuropsicologia',
                'Psicologia Infantil',
                'Terapia Comportamental',
                'Psicologia Escolar'
            ]),
            'description' => fake()->sentence(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}