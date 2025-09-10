<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class KidSeeder extends Seeder
{
    public function run()
    {
        // Buscar responsáveis disponíveis (usar modelo Responsible ao invés de User)
        $responsibles = \App\Models\Responsible::limit(5)->get();
        
        // Buscar profissionais disponíveis
        $professional1 = Professional::where('registration_number', 'CRM12345')->first();
        $professional2 = Professional::where('registration_number', 'CRM67890')->first();
        
        // Lista de profissionais para distribuir
        $professionals = collect([$professional1, $professional2])->filter();

        // Dados realistas para criar 20 crianças
        $kidsData = [
            ['name' => 'Ana Clara Silva', 'age' => 6, 'gender' => 'F', 'ethnicity' => 'branco'],
            ['name' => 'Pedro Santos', 'age' => 4, 'gender' => 'M', 'ethnicity' => 'pardo'],
            ['name' => 'Maria Eduarda Costa', 'age' => 5, 'gender' => 'F', 'ethnicity' => 'branco'],
            ['name' => 'João Gabriel Lima', 'age' => 7, 'gender' => 'M', 'ethnicity' => 'negro'],
            ['name' => 'Sofia Oliveira', 'age' => 3, 'gender' => 'F', 'ethnicity' => 'pardo'],
            ['name' => 'Lucas Fernandes', 'age' => 8, 'gender' => 'M', 'ethnicity' => 'branco'],
            ['name' => 'Isabela Rodrigues', 'age' => 4, 'gender' => 'F', 'ethnicity' => 'pardo'],
            ['name' => 'Matheus Almeida', 'age' => 6, 'gender' => 'M', 'ethnicity' => 'branco'],
            ['name' => 'Júlia Pereira', 'age' => 5, 'gender' => 'F', 'ethnicity' => 'negro'],
            ['name' => 'Gabriel Souza', 'age' => 7, 'gender' => 'M', 'ethnicity' => 'pardo'],
            ['name' => 'Valentina Carvalho', 'age' => 3, 'gender' => 'F', 'ethnicity' => 'branco'],
            ['name' => 'Davi Nascimento', 'age' => 8, 'gender' => 'M', 'ethnicity' => 'negro'],
            ['name' => 'Alice Barbosa', 'age' => 4, 'gender' => 'F', 'ethnicity' => 'pardo'],
            ['name' => 'Arthur Ribeiro', 'age' => 6, 'gender' => 'M', 'ethnicity' => 'branco'],
            ['name' => 'Helena Gomes', 'age' => 5, 'gender' => 'F', 'ethnicity' => 'pardo'],
            ['name' => 'Bernardo Dias', 'age' => 7, 'gender' => 'M', 'ethnicity' => 'negro'],
            ['name' => 'Laura Cardoso', 'age' => 3, 'gender' => 'F', 'ethnicity' => 'branco'],
            ['name' => 'Heitor Martins', 'age' => 8, 'gender' => 'M', 'ethnicity' => 'pardo'],
            ['name' => 'Manuela Rocha', 'age' => 4, 'gender' => 'F', 'ethnicity' => 'negro'],
            ['name' => 'Teodoro Moreira', 'age' => 6, 'gender' => 'M', 'ethnicity' => 'branco'],
        ];

        $firstUser = User::first();
        $createdBy = $firstUser ? $firstUser->id : 1;

        // Criar as crianças
        foreach ($kidsData as $index => $kidData) {
            // Calcular data de nascimento baseada na idade
            $birthDate = now()->subYears($kidData['age'])
                ->subMonths(rand(0, 11))
                ->subDays(rand(0, 28));

            // Selecionar responsável de forma cíclica
            $responsible = $responsibles->isNotEmpty() ? $responsibles->get($index % $responsibles->count()) : null;
            
            // Criar a criança
            $kid = Kid::create([
                'name' => $kidData['name'],
                'birth_date' => $birthDate->format('d/m/Y'),
                'gender' => $kidData['gender'],
                'ethnicity' => $kidData['ethnicity'],
                'responsible_id' => $responsible ? $responsible->id : null,
                'created_by' => $createdBy,
                'created_at' => now()->subDays(rand(1, 90)), // Datas de criação variadas
                'updated_at' => now()->subDays(rand(0, 30)),
            ]);

            // Associar profissionais aleatoriamente
            if ($professionals->isNotEmpty()) {
                // Alguns kids podem ter múltiplos profissionais
                $numProfessionals = rand(1, min(2, $professionals->count()));
                $selectedProfessionals = $professionals->random($numProfessionals);
                
                foreach ($selectedProfessionals as $professional) {
                    $kid->professionals()->attach($professional->id, [
                        'created_at' => $kid->created_at,
                        'updated_at' => $kid->updated_at,
                    ]);
                }
            }
        }

        $this->command->info('✅ Criadas 20 crianças com dados variados para testes');
    }
}
