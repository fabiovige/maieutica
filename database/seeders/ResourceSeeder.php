<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
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

        foreach (Resources::RESOURCES as $resource) {
            Resource::create($resource);
        }
    }
}
