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
        $role1 = Role::create([
            'name' => 'Super Admin',
            'role' => 'ROLE_SUPER_ADMIN',
            'created_by' => 1,
        ]);

        $role2 = Role::create([
            'name' => 'Admin',
            'role' => 'ROLE_ADMIN',
            'created_by' => 1,
        ]);

        foreach (Resources::RESOURCES as $resource) {
            Resource::create($resource)->roles()->sync([2]);
        }

    }
}
