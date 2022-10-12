<?php

namespace Database\Seeders;

use App\Models\Ability;
use Illuminate\Database\Seeder;

class AbilitySeeder extends Seeder
{
    const ABILITIES = [
        ['resource_id' => 1, 'ability' => 'users.index', 'name' => 'Listar'],
        ['resource_id' => 1, 'ability' => 'users.destroy', 'name' => 'Remover'],
        ['resource_id' => 1, 'ability' => 'users.store', 'name' => 'Cadastrar'],
        ['resource_id' => 1, 'ability' => 'users.update', 'name' => 'Atualizar'],

        ['resource_id' => 2, 'ability' => 'kids.index', 'name' => 'Listar'],
        ['resource_id' => 2, 'ability' => 'kids.destroy', 'name' => 'Remover'],
        ['resource_id' => 2, 'ability' => 'kids.store', 'name' => 'Cadastrar'],
        ['resource_id' => 2, 'ability' => 'kids.update', 'name' => 'Atualizar'],

        ['resource_id' => 3, 'ability' => 'roles.index', 'name' => 'Listar'],
        ['resource_id' => 3, 'ability' => 'roles.destroy', 'name' => 'Remover'],
        ['resource_id' => 3, 'ability' => 'roles.store', 'name' => 'Cadastrar'],
        ['resource_id' => 3, 'ability' => 'roles.update', 'name' => 'Atualizar'],

        ['resource_id' => 4, 'ability' => 'checklists.index', 'name' => 'Listar'],
        ['resource_id' => 4, 'ability' => 'checklists.destroy', 'name' => 'Remover'],
        ['resource_id' => 4, 'ability' => 'checklists.store', 'name' => 'Cadastrar'],
        ['resource_id' => 4, 'ability' => 'checklists.update', 'name' => 'Atualizar'],
        ['resource_id' => 4, 'ability' => 'checklists.fill', 'name' => 'Preencher'],

        ['resource_id' => 5, 'ability' => 'responsibles.index', 'name' => 'Listar'],
        ['resource_id' => 5, 'ability' => 'responsibles.destroy', 'name' => 'Remover'],
        ['resource_id' => 5, 'ability' => 'responsibles.store', 'name' => 'Cadastrar'],
        ['resource_id' => 5, 'ability' => 'responsibles.update', 'name' => 'Atualizar'],
    ];

    public function run()
    {
        foreach (self::ABILITIES as $ability) {
            Ability::create($ability)->roles()->sync([1,2]);
        }
    }
}
