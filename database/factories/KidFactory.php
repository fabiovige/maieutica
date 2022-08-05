<?php

namespace Database\Factories;

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
        $date = $this->faker->dateTimeBetween('2020-01-01', '2021-12-31');

        return [
            'name' => $this->faker->name,
            'birth_date' => $date,
            'user_id' => 1,
            'created_by' => 1,
        ];
    }
}
