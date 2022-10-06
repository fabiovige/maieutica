<?php

namespace Database\Seeders;

use App\Models\Competence;
use App\Models\Kid;
use App\Models\Plane;
use Illuminate\Database\Seeder;

class PlaneSeeder extends Seeder
{

    public function run()
    {
        // kid
        $kid = Kid::pluck('id');

        // planes
        for ($i = 1; $i <= 4; $i++) {
            $this->insertPlane($kid);
        }


    }

    public function insertPlane($kid)
    {
        // planes
        $plane = Plane::create([
            'kid_id' => $kid->random(),
            'created_by' => 1
        ]);

        // competences
        $indice = rand(1, 20);
        $arrCompetences = [];
        for ($i = 1; $i <= $indice; $i++) {
            $arrCompetences[] = $i;
        }

        $plane->competences()->sync($arrCompetences);
    }
}
