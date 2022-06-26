<?php

namespace Database\Seeders;

abstract class Resources
{
    public const RESOURCES = [
        ['name' => 'Crianças Listar', 'ability' => 'kids.index'],
        ['name' => 'Crianças Excluir', 'ability' => 'kids.destroy'],
        ['name' => 'Crianças Visualizar ', 'ability' => 'kids.show'],
        ['name' => 'Crianças Registrar', 'ability' => 'kids.store'],
        ['name' => 'Criançaa Atualizar', 'ability' => 'kids.update'],
        ['name' => 'Crianças Tela de cadastro', 'ability' => 'kids.create'],
        ['name' => 'Crianças Tela de edição', 'ability' => 'kids.edit'],

        ['name' => 'Usuários Listar ', 'ability' => 'users.index'],
        ['name' => 'Usuários Excluir', 'ability' => 'users.destroy'],
        ['name' => 'Usuários Visualizar', 'ability' => 'users.show'],
        ['name' => 'Usuários Registrar Atualizar', 'ability' => 'users.store'],
        ['name' => 'Usuários', 'ability' => 'users.update'],
        ['name' => 'Usuários Tela de cadastro', 'ability' => 'users.create'],
        ['name' => 'Usuários Tela de edição', 'ability' => 'users.edit'],

        ['name' => 'Papéis Listar', 'ability' => 'roles.index'],
        ['name' => 'Papéis Excluir', 'ability' => 'roles.destroy'],
        ['name' => 'Papéis Visualizar', 'ability' => 'roles.show'],
        ['name' => 'Papéis Registrar', 'ability' => 'roles.store'],
        ['name' => 'Papéis Atualizar', 'ability' => 'roles.update'],
        ['name' => 'Papéis Tela de recursos', 'ability' => 'roles.resources'],
        ['name' => 'Papéis Atualizar recursos', 'ability' => 'roles.resources.update'],
        ['name' => 'Papéis Tela de cadastro', 'ability' => 'roles.create'],
        ['name' => 'Papéis Tela de edição', 'ability' => 'roles.edit'],
    ];
}
