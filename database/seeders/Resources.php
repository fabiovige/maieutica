<?php

namespace Database\Seeders;

abstract class Resources
{
    public const RESOURCES = [
        ['name' => 'Listar crianças', 'ability' => 'kids.index'],
        ['name' => 'Remover crianças', 'ability' => 'kids.destroy'],
        ['name' => 'Cadastrar crianças', 'ability' => 'kids.store'],
        ['name' => 'Editar crianças', 'ability' => 'kids.update'],

        ['name' => 'Listar usuários', 'ability' => 'users.index'],
        ['name' => 'Remover usuários', 'ability' => 'users.destroy'],
        ['name' => 'Cadastrar usuários', 'ability' => 'users.store'],
        ['name' => 'Editar usuários', 'ability' => 'users.update'],

        ['name' => 'Listar papéis', 'ability' => 'roles.index'],
        ['name' => 'Remover papéis', 'ability' => 'roles.destroy'],
        ['name' => 'Cadastrar papeís', 'ability' => 'roles.store'],
        ['name' => 'Editar papéis', 'ability' => 'roles.update'],

    ];
}
