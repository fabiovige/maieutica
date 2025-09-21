<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Responsible;
use Illuminate\Database\Seeder;

class ResponsibleSeeder extends Seeder
{
    public function run()
    {
        // Criar primeiro usuário responsável
        $responsibleUser1 = User::firstOrCreate(
            ['email' => 'responsible1@example.com'],
            [
                'name' => 'Maria da Silva',
                'password' => bcrypt('password'),
                'created_by' => 1,
            ]
        );
        $responsibleUser1->assignRole('pais');

        // Criar registro na tabela responsibles
        Responsible::firstOrCreate(
            ['user_id' => $responsibleUser1->id],
            [
                'name' => $responsibleUser1->name,
                'email' => $responsibleUser1->email,
                'cpf' => '12345678901',
                'cell' => '11999999001',
                'created_by' => 1,
            ]
        );

        // Criar segundo usuário responsável
        $responsibleUser2 = User::firstOrCreate(
            ['email' => 'responsible2@example.com'],
            [
                'name' => 'João Santos',
                'password' => bcrypt('password'),
                'created_by' => 1,
            ]
        );
        $responsibleUser2->assignRole('pais');

        // Criar registro na tabela responsibles
        Responsible::firstOrCreate(
            ['user_id' => $responsibleUser2->id],
            [
                'name' => $responsibleUser2->name,
                'email' => $responsibleUser2->email,
                'cpf' => '12345678902',
                'cell' => '11999999002',
                'created_by' => 1,
            ]
        );
    }
}
