<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    const RESOURCES = [
        ['name' => 'Usuários'],
        ['name' => 'Crianças'],
        ['name' => 'Papéis'],
        ['name' => 'Checklists'],
    ];

    public function run()
    {
        foreach (self::RESOURCES as $resource) {
            Resource::create($resource);
        }
    }
}
