<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Responsible;
use Illuminate\Database\Seeder;

class ResponsibleSeeder extends Seeder
{
    public function run()
    {
        $adminUser = User::where('email', 'fabiovige@gmail.com')->first();
        if (!$adminUser) {
            $this->command->error('User admin não encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $responsibles = [
            [
                'name' => 'Maria da Silva',
                'email' => 'maria.silva@example.com',
                'cpf' => '123.456.789-01',
                'cell' => '(11) 98765-4321',
            ],
            [
                'name' => 'João Santos',
                'email' => 'joao.santos@example.com',
                'cpf' => '234.567.890-12',
                'cell' => '(11) 99876-5432',
            ],
            [
                'name' => 'Ana Paula Costa',
                'email' => 'ana.costa@example.com',
                'cpf' => '345.678.901-23',
                'cell' => '(21) 98888-7777',
            ],
            [
                'name' => 'Carlos Ferreira',
                'email' => 'carlos.ferreira@example.com',
                'cpf' => '456.789.012-34',
                'cell' => '(31) 97777-6666',
            ],
            [
                'name' => 'Beatriz Lima',
                'email' => 'beatriz.lima@example.com',
                'cpf' => '567.890.123-45',
                'cell' => '(41) 96666-5555',
            ],
        ];

        foreach ($responsibles as $index => $responsibleData) {
            $user = User::create([
                'name' => $responsibleData['name'],
                'email' => $responsibleData['email'],
                'password' => bcrypt('password123'),
                'created_by' => $adminUser->id,
            ]);

            $user->assignRole('pais');

            Responsible::create([
                'user_id' => $user->id,
                'name' => $responsibleData['name'],
                'email' => $responsibleData['email'],
                'cpf' => $responsibleData['cpf'],
                'cell' => $responsibleData['cell'],
                'created_by' => $adminUser->id,
            ]);
        }

        $this->command->info('✅ 5 responsáveis criados com sucesso');
    }
}
