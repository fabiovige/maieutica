<?php

namespace Database\Seeders;

use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfessionalSeeder extends Seeder
{
    public function run()
    {
        // Buscar a especialidade já criada
        $specialty = Specialty::where('name', 'Pediatria')->firstOrFail();

        // Criar usuários profissionais
        $users = [
            [
                'email' => 'professional1@example.com',
                'name' => 'Dr. João Silva',
                'registration' => 'CRM12345',
                'bio' => 'Médico pediatra com 10 anos de experiência'
            ],
            [
                'email' => 'professional2@example.com',
                'name' => 'Dra. Maria Santos',
                'registration' => 'CRM67890',
                'bio' => 'Médica pediatra especialista em desenvolvimento infantil'
            ]
        ];

        foreach ($users as $userData) {
            // 1. Criar usuário
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'created_by' => 1,
                ]
            );

            // 2. Atribuir papel de professional
            $user->assignRole('professional');

            // 3. Criar registro professional
            $professional = Professional::firstOrCreate(
                ['registration_number' => $userData['registration']],
                [
                    'specialty_id' => $specialty->id,
                    'bio' => $userData['bio'],
                    'created_by' => 1,
                ]
            );

            // 4. Criar relacionamento na tabela pivot
            $user->professional()->sync([$professional->id]);
        }
    }
}
