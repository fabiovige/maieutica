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
        $kids = Kid::limit(5)->pluck('id'); // Limitar a 5 crianças para seeds mais rápidos

        // Cache das competências por nível para evitar queries repetidas
        $competencesByLevel = [];
        for ($i = 1; $i <= 4; $i++) {
            $competencesByLevel[$i] = Competence::where('level_id', $i)->pluck('id')->toArray();
        }

        $checklistCompetenceData = [];

        foreach ($kids as $kidId) {
            // Criar apenas 1 checklist por criança para reduzir volume
            $checklistId = Checklist::insertGetId([
                'kid_id' => $kidId,
                'level' => 4,
                'created_by' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            // Preparar relações checklist-competence para inserção em lote
            for ($level = 1; $level <= 4; $level++) {
                foreach ($competencesByLevel[$level] as $competenceId) {
                    $checklistCompetenceData[] = [
                        'checklist_id' => $checklistId,
                        'competence_id' => $competenceId,
                        'note' => rand(1, 3),
                    ];
                }
            }
        }

        // Inserção em lote das relações
        if (!empty($checklistCompetenceData)) {
            // Dividir em chunks para evitar problemas de memória
            $chunks = array_chunk($checklistCompetenceData, 1000);
            foreach ($chunks as $chunk) {
                \DB::table('checklist_competence')->insert($chunk);
            }
        }
    }
}
