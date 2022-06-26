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
            'name' => 'Fabio',
            'email' => 'fabio@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 1,
        ]);
        $user1->role()->associate(1);
        $user1->save();

        $user2 = User::create([
            'name' => 'Ricardo',
            'email' => 'ricardo@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'created_by' => 1,
        ]);
        $user2->role()->associate(2);
        $user2->save();
//
//        $user3 = User::create([
//            'name' => 'Flavia',
//            'email' => 'flavia@gmail.com',
//            'email_verified_at' => now(),
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
//            'remember_token' => Str::random(10),
//            'created_by' => 2,
//        ]);
//        $user3->role()->associate(3);
//        $user3->save();
//
//        $user4 = User::create([
//            'name' => 'Maria',
//            'email' => 'maria@gmail.com',
//            'email_verified_at' => now(),
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
//            'remember_token' => Str::random(10),
//            'created_by' => 3,
//        ]);
//        $user4->role()->associate(4);
//        $user4->save();
//
//        $user5 = User::create([
//            'name' => 'João pai teste',
//            'email' => 'jose@gmail.com',
//            'email_verified_at' => now(),
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
//            'remember_token' => Str::random(10),
//            'created_by' => 3,
//        ]);
//        $user5->role()->associate(5);
//        $user5->save();
//
//        $user6 = User::create([
//            'name' => 'Visitante',
//            'email' => 'visitante@gmail.com',
//            'email_verified_at' => now(),
//            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
//            'remember_token' => Str::random(10),
//            'created_by' => 2,
//        ]);
//        $user6->role()->associate(6);
//        $user6->save();

        //User::factory()->count(50)->create();
    }
}
