<?php

namespace Database\Seeders;

use App\Models\Responsible;
use Illuminate\Database\Seeder;

class ResponsibleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Responsible::factory()->count(2)->create();
    }
}
