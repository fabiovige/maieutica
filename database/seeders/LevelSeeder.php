<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run()
    {
        $levels = [
            ['level'=>1, 'name'=>'Nível 1'],
            ['level'=>2, 'name'=>'Nível 2'],
            ['level'=>3, 'name'=>'Nível 3'],
            ['level'=>4, 'name'=>'Nível 4'],
        ];

        $domains = [
            1 => [1,2,3,4,6,9,11,12,13,14,15,18,19],
            2 => [1,2,3,5,7,8,9,11,12,13,14,16,17,18,19],
            3 => [1,2,3,7,10,13,14,15,18,19],
            4 => [1,2,3,6,10,15,18,19]
        ];

        foreach($levels as $level) {
            $l = Level::create($level);
            $l->domains()->sync($domains[$level['level']]);
        }
    }
}
