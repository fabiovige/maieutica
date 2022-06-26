<?php

namespace Database\Seeders;

use App\Models\Kid;
use Illuminate\Database\Seeder;

class KidSeeder extends Seeder
{
    public function run()
    {
        Kid::factory()->count(25)->create();
    }
}
