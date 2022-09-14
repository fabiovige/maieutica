<?php

namespace Database\Seeders;

use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Kid;
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
        $kid = Kid::pluck('id');

        // checklists
        foreach([1,2,3,4] as $c => $v) {

            $checklist = Checklist::create([
                'kid_id' => $kid->random(),
                'level' => 4,
                'created_by' => 1
            ]);

            // levels
            foreach([1,2,3,4] as $c => $level) {
                $components = Competence::where('level_id', '=', $level)->pluck('id')->toArray();

                $notes = [];
                // competences
                foreach($components as $c => $v) {
                    $notes[$v] = ['note' => rand(1, 4)];
                }

                $checklist->competences()->syncWithoutDetaching($notes);
            }
        }
    }
}
