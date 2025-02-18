<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ResponsibleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
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
    }
}
