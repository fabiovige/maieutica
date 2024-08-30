<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\Plane;
use Illuminate\Database\Seeder;

class PlaneSeeder extends Seeder
{
    public function run()
    {
        // kid
        $arrKid = Kid::pluck('id');
        foreach ($arrKid as $key => $kid_id) {
            $data = Kid::find($kid_id);
            if ($data->checklists()->count() > 0) {
                $this->insertPlane($kid_id);
            }
        }
    }

    public function insertPlane($kid_id)
    {
        // planes
        $plane = Plane::create([
            'kid_id' => $kid_id,
            'created_by' => 1,
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
