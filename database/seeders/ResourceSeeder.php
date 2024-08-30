<?php

namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public const RESOURCES = [
        ['name' => 'Usuários'],
        ['name' => 'Crianças'],
        ['name' => 'Papéis'],
        ['name' => 'Checklists'],
        ['name' => 'Responsáveis'],
    ];

    public function run()
    {
        foreach (self::RESOURCES as $resource) {
            Resource::create($resource);
        }
    }
}
