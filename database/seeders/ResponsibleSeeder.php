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
            ['email' => 'user05@gmail.com'],
            [
                'name' => 'Maria da Silva User 05',
                'password' => bcrypt('password'),
                'created_by' => 1,
            ]
        );
        $responsibleUser1->assignRole('responsavel');

        // Criar segundo usuário responsável
        $responsibleUser2 = User::firstOrCreate(
            ['email' => 'user06@gmail.com'],
            [
                'name' => 'João Santos User 06',
                'password' => bcrypt('password'),
                'created_by' => 1,
            ]
        );
        $responsibleUser2->assignRole('responsavel');

    }
}
