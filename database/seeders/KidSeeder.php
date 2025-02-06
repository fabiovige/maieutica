<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class KidSeeder extends Seeder
{
    public function run()
    {
        // Criar roles se não existirem
        $professionalRole = Role::firstOrCreate(['name' => 'professional']);
        $parentRole = Role::firstOrCreate(['name' => 'pais']);

        // Criar alguns profissionais se não existirem
        if (User::whereHas('roles', function($q) {
            $q->where('name', 'professional');
        })->count() == 0) {
            for ($i = 0; $i < 3; $i++) {
                $professional = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => bcrypt('password'),
                    'created_by' => 1,
                ]);
                $professional->assignRole('professional');
            }
        }

        // Criar alguns responsáveis se não existirem
        if (User::whereHas('roles', function($q) {
            $q->where('name', 'pais');
        })->count() == 0) {
            for ($i = 0; $i < 5; $i++) {
                $parent = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => bcrypt('password'),
                    'created_by' => 1,
                ]);
                $parent->assignRole('pais');
            }
        }

        // Obter profissionais e responsáveis
        $professionals = User::whereHas('roles', function($q) {
            $q->where('name', 'professional');
        })->get();

        $responsibles = User::whereHas('roles', function($q) {
            $q->where('name', 'pais');
        })->get();

        // Criar crianças
        for ($i = 0; $i < 10; $i++) {
            $kid = Kid::create([
                'name' => fake()->name(),
                'birth_date' => fake()->date(),
                'responsible_id' => $responsibles->random()->id,
                'created_by' => 1,
            ]);

            // Atribuir um profissional principal
            $kid->professionals()->attach($professionals->random()->id, [
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Chance de 30% de adicionar um segundo profissional
            if (fake()->boolean(30) && $professionals->count() > 1) {
                $secondProfessional = $professionals->except($kid->professionals->pluck('id')->toArray())->random();
                $kid->professionals()->attach($secondProfessional->id, [
                    'is_primary' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
