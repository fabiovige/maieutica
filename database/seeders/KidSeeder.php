<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class KidSeeder extends Seeder
{
    public function run()
    {
        // Criar uma especialidade se não existir
        $specialty = Specialty::firstOrCreate(
            ['name' => 'Pediatria'],
            [
                'description' => 'Especialidade médica dedicada aos cuidados da criança e do adolescente',
                'created_by' => 1,
            ]
        );

        // Criar usuário profissional se não existir
        $professionalUser = User::firstOrCreate(
            ['email' => 'professional@example.com'],
            [
                'name' => 'Dr. Professional',
                'password' => bcrypt('password'),
                'created_by' => 1,
            ]
        );
        $professionalUser->assignRole('professional');

        // Criar o registro professional
        $professional = Professional::firstOrCreate(
            ['registration_number' => 'CRM12345'],
            [
                'specialty_id' => $specialty->id,
                'bio' => 'Médico pediatra com 10 anos de experiência',
                'created_by' => 1,
            ]
        );

        // Associar usuário ao professional
        $professional->user()->sync([$professionalUser->id]);

        // Criar usuário responsável se não existir
        $responsibleUser = User::firstOrCreate(
            ['email' => 'responsible@example.com'],
            [
                'name' => 'Responsible Parent',
                'password' => bcrypt('password'),
                'created_by' => 1,
            ]
        );
        $responsibleUser->assignRole('pais');

        // Criar a criança
        $kid = Kid::create([
            'name' => 'Test Kid',
            'birth_date' => now()->subYears(5),
            'gender' => 'M',
            'ethnicity' => 'branco',
            'responsible_id' => $responsibleUser->id,
            'created_by' => 1,
        ]);

        // Associar o profissional à criança
        $kid->professionals()->attach($professional->id, [
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
