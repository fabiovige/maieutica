<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            //ResourceSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            KidSeeder::class,
            CompetenceSeeder::class,
            CompetenceItemSeeder::class,
            ChecklistSeeder::class,
        ]);
    }
}
