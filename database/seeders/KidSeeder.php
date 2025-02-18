<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Seeder;

class KidSeeder extends Seeder
{
    public function run()
    {
        // Buscar responsáveis
        $responsible1 = User::where('email', 'responsible1@example.com')->first();
        $responsible2 = User::where('email', 'responsible2@example.com')->first();

        // Criar primeira criança
        $kid1 = Kid::create([
            'name' => 'Ana Silva',
            'birth_date' => now()->subYears(6),
            'gender' => 'F',
            'ethnicity' => 'branco',
            'responsible_id' => $responsible1->id,
            'created_by' => 1,
        ]);

        // Criar segunda criança
        $kid2 = Kid::create([
            'name' => 'Pedro Santos',
            'birth_date' => now()->subYears(4),
            'gender' => 'M',
            'ethnicity' => 'pardo',
            'responsible_id' => $responsible2->id,
            'created_by' => 1,
        ]);

        // Associar profissionais às crianças
        $professional1 = Professional::where('registration_number', 'CRM12345')->first();
        $professional2 = Professional::where('registration_number', 'CRM67890')->first();

        if ($professional1 && $professional2) {
            $kid1->professionals()->attach($professional1->id);
            $kid2->professionals()->attach($professional2->id);
        }
    }
}
