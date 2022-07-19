<?php

namespace Database\Seeders;

use App\Models\Checklist;
use Illuminate\Database\Seeder;

class ChecklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user1 = Checklist::create([
            'kid_id' => 1,
            'level' => 4,
            'status' => 'aberto',
            'description' => 'checklist criado....',
            'created_by' => 1,
        ]);
    }
}
