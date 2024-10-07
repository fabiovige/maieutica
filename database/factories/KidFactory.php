<?php

namespace Database\Factories;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class KidFactory extends Factory
{
    protected $model = Kid::class;

    public function definition()
    {
        // Gera uma data de nascimento aleatória entre 01/01/2019 e 31/12/2021
        $birthDate = $this->faker->dateTimeBetween('2018-01-01', '2023-12-31')->format('d/m/Y');

        // Recupera os IDs dos usuários com a role 'pais' (responsável)
        $responsibleIds = User::whereHas('roles', function ($query) {
            $query->where('name', 'pais');
        })->pluck('id');

        // Recupera os IDs dos usuários com a role 'Professional'
        $professionalIds = User::whereHas('roles', function ($query) {
            $query->where('name', 'Professional');
        })->pluck('id');

        // Seleciona um ID aleatório para o responsável, ou null se não houver
        $responsibleId = $responsibleIds->isNotEmpty() ? $responsibleIds->random() : null;

        // Seleciona um ID aleatório para o professional, ou null se não houver
        $professionalId = $professionalIds->isNotEmpty() ? $professionalIds->random() : null;

        // Seleciona um usuário aleatório para ser o criador, tipicamente um admin ou superadmin
        $createdBy = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'superadmin']);
        })->inRandomOrder()->first()->id ?? null;

        return [
            'name' => $this->faker->name,
            'birth_date' => $birthDate,
            'responsible_id' => $responsibleId, // Associando ao responsável (pais)
            'profession_id' => $professionalId, // Associando ao professional
            'created_by' => $createdBy,
            // Adicione outros campos preenchíveis conforme necessário
        ];
    }
}
