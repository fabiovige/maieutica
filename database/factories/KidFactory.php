<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory
 */
class KidFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $date = $this->faker->randomElement(['01/01/2019', '31/12/2021']);

        // Filtra os responsáveis (ROLE_PAIS)
        $responsible = User::where('role_id', User::ROLE_PAIS)->pluck('id'); // ROLE_PAIS é 4

        // Filtra os profissionais (ROLE_PROFESSION)
        $professional = User::where('role_id', User::ROLE_PROFESSION)->pluck('id'); // ROLE_PROFESSION é 5

        return [
            'name' => $this->faker->name,
            'birth_date' => $date,
            'responsible_id' => $responsible->random(), // Associando ao responsável
            'profession_id' => $professional->random(), // Associando ao profissional
            'created_by' => 2,
        ];
    }
}
