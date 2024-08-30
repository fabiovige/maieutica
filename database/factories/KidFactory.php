<?php

namespace Database\Factories;

use App\Models\Responsible;
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

        $user = User::whereNotIn('id', [1, 2])->pluck('id');
        $responsible = Responsible::pluck('id');

        return [
            'name' => $this->faker->name,
            'birth_date' => $date,
            'user_id' => $user->random(),
            'responsible_id' => $responsible->random(),
            'created_by' => 2,
        ];
    }
}
