<?php

namespace Database\Factories;

use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfessionalFactory extends Factory
{
    protected $model = Professional::class;

    public function definition(): array
    {
        return [
            'registration_number' => 'CRP' . fake()->unique()->numerify('######'),
            'bio' => fake()->paragraph(3),
            'specialty_id' => Specialty::factory(),
            'created_by' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function withUser(User $user = null): static
    {
        return $this->afterCreating(function (Professional $professional) use ($user) {
            $userToAttach = $user ?? User::factory()->create();
            $userToAttach->assignRole('professional');
            $professional->user()->attach($userToAttach->id);
        });
    }

    public function withSpecialty(Specialty $specialty): static
    {
        return $this->state(fn (array $attributes) => [
            'specialty_id' => $specialty->id,
        ]);
    }
}