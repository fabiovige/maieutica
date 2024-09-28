<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Definição dos usuários a serem criados
        $users = [
            [
                'name' => 'Fabio Martins',
                'email' => 'fabiovige@gmail.com',
                'password' => 'password', // senha simples para teste
                'created_by' => null, // ou o ID do criador, se aplicável
                'role' => 'superadmin',
            ],
            [
                'name' => 'Ricardo Nascimento',
                'email' => 'ricardo@mailinator.com',
                'password' => 'password',
                'created_by' => 1, // criado por superadmin
                'role' => 'admin',
            ],
            [
                'name' => 'Flávia Moreno',
                'email' => 'flavia@mailinator.com',
                'password' => 'password',
                'created_by' => 2, // criado por admin
                'role' => 'Professional',
            ],
            [
                'name' => 'Valéria Nunes',
                'email' => 'valeria@mailinator.com',
                'password' => 'password',
                'created_by' => 2, // criado por admin
                'role' => 'pais',
            ],
        ];

        foreach ($users as $userData) {
            // Atribuir role antes de criar o usuário, se necessário
            $roleName = $userData['role'];
            unset($userData['role']);

            // Criar o usuário
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'created_by' => $userData['created_by'],
                // Adicione outros campos conforme necessário
            ]);

            // Atribuir o role usando o nome
            $user->assignRole($roleName);
        }
    }
}
