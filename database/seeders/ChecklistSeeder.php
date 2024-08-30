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
        foreach ([1, 2] as $c => $v) {

            // levels
            $indice = 4;
            $arrLevel = [];
            for ($i = 1; $i <= $indice; $i++) {
                $arrLevel[] = $i;
            }

            $checklist = Checklist::create([
                'kid_id' => $kid->random(),
                'level' => $indice,
                'created_by' => 1,
            ]);

            foreach ($arrLevel as $c => $level) {
                $components = Competence::where('level_id', '=', $level)->pluck('id')->toArray();

                $notes = [];
                // competences
                foreach ($components as $c => $v) {
                    $notes[$v] = ['note' => rand(0, 3)];
                }

                $checklist->competences()->syncWithoutDetaching($notes);
            }
        }
    }
}
