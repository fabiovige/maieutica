<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'Super Admin',
            'role' => 'ROLE_SUPER_ADMIN',
            'created_by' => 1,
        ]);

        Role::create([
            'name' => 'Admin',
            'role' => 'ROLE_ADMIN',
            'created_by' => 1,
        ]);

        Role::create([
            'name' => 'Pais',
            'role' => 'ROLE_PAIS',
            'created_by' => 2,
        ]);

        Role::create([
            'name' => 'Profession',
            'role' => 'ROLE_PROFESSION',
            'created_by' => 2,
        ]);

    }
}
