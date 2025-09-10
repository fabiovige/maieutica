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
                'name' => 'Fabio Martins',
                'email' => 'fabiovige@gmail.com',
                'password' => 'password', // senha simples para teste
                'created_by' => null, // ou o ID do criador, se aplicável
                'role' => 'superadmin',
                'phone' => '(11) 99999-0001',
                'postal_code' => '01310-100',
                'street' => 'Av. Paulista',
                'number' => '1000',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            [
                'name' => 'Ricardo Nascimento',
                'email' => 'ricardo@mailinator.com',
                'password' => 'password',
                'created_by' => 1, // criado por superadmin
                'role' => 'admin',
                'phone' => '(11) 99999-0002',
                'postal_code' => '04038-001',
                'street' => 'Rua Fidêncio Ramos',
                'number' => '302',
                'neighborhood' => 'Vila Olímpia',
                'city' => 'São Paulo',
                'state' => 'SP',
            ],
            [
                'name' => 'Flávia Moreno',
                'email' => 'flavia@mailinator.com',
                'password' => 'password',
                'created_by' => 2, // criado por admin
                'role' => 'professional',
                'phone' => '(11) 99999-0003',
                'postal_code' => '22071-900',
                'street' => 'Av. Atlântica',
                'number' => '1702',
                'neighborhood' => 'Copacabana',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
            ],
            [
                'name' => 'Maria da Silva',
                'email' => 'maria@maildrop.cc',
                'password' => 'password',
                'created_by' => 2, // criado por admin
                'role' => 'pais',
                'phone' => '(11) 99999-0004',
                'postal_code' => '30130-010',
                'street' => 'Av. Afonso Pena',
                'number' => '867',
                'neighborhood' => 'Centro',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
            ],
        ];

        User::flushEventListeners();
        foreach ($users as $userData) {
            // Atribuir role antes de criar o usuário, se necessário
            $roleName = $userData['role'];
            unset($userData['role']);

            // Criar o usuário usando firstOrCreate
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'created_by' => $userData['created_by'],
                    'phone' => $userData['phone'] ?? null,
                    'postal_code' => $userData['postal_code'] ?? null,
                    'street' => $userData['street'] ?? null,
                    'number' => $userData['number'] ?? null,
                    'complement' => $userData['complement'] ?? null,
                    'neighborhood' => $userData['neighborhood'] ?? null,
                    'city' => $userData['city'] ?? null,
                    'state' => $userData['state'] ?? null,
                ]
            );

            // Atribuir o role usando o nome se ainda não tiver
            if (!$user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }
        }
        User::observe(UserObserver::class);
    }
}
