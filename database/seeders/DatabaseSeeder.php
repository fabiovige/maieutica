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
            RoleSeeder::class,
            UserSeeder::class,
            ResponsibleSeeder::class,
            KidSeeder::class,
            ResourceSeeder::class,
            AbilitySeeder::class,
            DomainSeeder::class,
            LevelSeeder::class,
            CompetenceSeeder::class,
            ChecklistSeeder::class,
            PlaneSeeder::class,
        ]);
    }
}
