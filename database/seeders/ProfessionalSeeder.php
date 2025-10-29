<?php

namespace Database\Seeders;

use App\Models\Professional;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfessionalSeeder extends Seeder
{
    public function run()
    {
        // Buscar a especialidade já criada
        $specialty = Specialty::where('name', 'Pediatria')->firstOrFail();

        $user = User::where('email','user03@gmail.com')->firstOrFail();

        // 3. Criar registro professional
        $professional = Professional::firstOrCreate(
            ['registration_number' => 'ABCD123'],
            [
                'specialty_id' => $specialty->id,
                'bio' => 'Uma breve descrição',
                'created_by' => 1,
            ]
        );

        // 4. Criar relacionamento na tabela pivot
        $user->professional()->sync([$professional->id]);

    }
}
