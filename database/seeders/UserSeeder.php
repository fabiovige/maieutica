<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = User::create([
            'name' => 'Fabio Martins',
            'email' => 'fabiovige@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 1,
        ]);
        $user1->role()->associate(1);
        $user1->save();

        $user2 = User::create([
            'name' => 'Ricardo Nascimento',
            'email' => 'ricardo@mailinator.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 1,
        ]);
        $user2->role()->associate(2);
        $user2->save();

        $user3 = User::create([
            'name' => 'Flávia Moreno',
            'email' => 'flavia@mailinator.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 2,
        ]);
        $user3->role()->associate(4);
        $user3->save();

        $user3 = User::create([
            'name' => 'João Pedro',
            'email' => 'joao@mailinator.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 2,
        ]);
        $user3->role()->associate(3);
        $user3->save();
    }
}
