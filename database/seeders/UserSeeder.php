<?php

namespace Database\Seeders;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Definição dos usuários a serem criados
        $users = [
            [
                'name' => 'Fabio User 01',
                'email' => 'user01@gmail.com',
                'password' => 'password', // senha simples para teste
                'created_by' => 1, // ou o ID do criador, se aplicável
                'role' => 'admin',
            ],
            [
                'name' => 'Ricardo User 02',
                'email' => 'user02@gmail.com',
                'password' => 'password',
                'created_by' => 1, // criado por admin
                'role' => 'admin',
            ],
            [
                'name' => 'Flávia User 03',
                'email' => 'user03@gmail.com',
                'password' => 'password',
                'created_by' => 1, // criado por admin
                'role' => 'profissional',
            ],
            [
                'name' => 'Maria User 04',
                'email' => 'user04@gmail.com',
                'password' => 'password',
                'created_by' => 1, // criado por admin
                'role' => 'responsavel',
            ],
        ];

        User::flushEventListeners();
        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'created_by' => $userData['created_by'],
            ]);

            $user->assignRole($roleName);
        }
        User::observe(UserObserver::class);
    }
}
