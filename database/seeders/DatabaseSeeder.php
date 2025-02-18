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
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            SpecialtySeeder::class,
            ProfessionalSeeder::class,
            ResponsibleSeeder::class,
            KidSeeder::class,
            DomainSeeder::class,
            LevelSeeder::class,
            CompetenceSeeder::class,
            ChecklistSeeder::class,
            //PlaneSeeder::class,
        ]);
    }
}
