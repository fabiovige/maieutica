<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run()
    {
        $specialties = [
            [
                'name' => 'Pediatria',
                'description' => 'Especialidade médica dedicada aos cuidados da criança e do adolescente',
                'created_by' => 1
            ],
            [
                'name' => 'Psicologia',
                'description' => 'Profissional que estuda o comportamento e processos mentais',
                'created_by' => 1
            ],
            [
                'name' => 'Fonoaudiologia',
                'description' => 'Profissional que trata distúrbios da comunicação',
                'created_by' => 1
            ],
            [
                'name' => 'Terapia Ocupacional',
                'description' => 'Profissional que promove a reabilitação física e mental',
                'created_by' => 1
            ]
        ];

        foreach ($specialties as $specialty) {
            Specialty::firstOrCreate(
                ['name' => $specialty['name']],
                $specialty
            );
        }
    }
}
