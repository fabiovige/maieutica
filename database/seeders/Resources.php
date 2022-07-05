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
        ['name' => 'Usuários Atualizar', 'ability' => 'users.update'],
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

        ['name' => 'Competências Listar ', 'ability' => 'competences.index'],
        ['name' => 'Competências Excluir', 'ability' => 'competences.destroy'],
        ['name' => 'Competências Visualizar', 'ability' => 'competences.show'],
        ['name' => 'Competências Registrar Atualizar', 'ability' => 'competences.store'],
        ['name' => 'Competências Atualizar', 'ability' => 'competences.update'],
        ['name' => 'Competências Tela de cadastro', 'ability' => 'competences.create'],
        ['name' => 'Competências Tela de edição', 'ability' => 'competences.edit'],

        ['name' => 'Competência Itens Listar ', 'ability' => 'competenceItems.index'],
        ['name' => 'Competência Itens Excluir', 'ability' => 'competenceItems.destroy'],
        ['name' => 'Competência Itens Visualizar', 'ability' => 'competenceItems.show'],
        ['name' => 'Competência Itens Registrar Atualizar', 'ability' => 'competenceItems.store'],
        ['name' => 'Competência Itens Atualizar', 'ability' => 'competenceItems.update'],
        ['name' => 'Competência Itens Tela de cadastro', 'ability' => 'competenceItems.create'],
        ['name' => 'Competência Itens Tela de edição', 'ability' => 'competenceItems.edit'],

        ['name' => 'Checklist Listar ', 'ability' => 'checklists.index'],
        ['name' => 'Checklist Excluir', 'ability' => 'checklists.destroy'],
        ['name' => 'Checklist Visualizar', 'ability' => 'checklists.show'],
        ['name' => 'Checklist Registrar Atualizar', 'ability' => 'checklists.store'],
        ['name' => 'Checklist Atualizar', 'ability' => 'checklists.update'],
        //['name' => 'Checklist Tela de cadastro', 'ability' => 'checklists.create'],
        ['name' => 'Checklist Tela de edição', 'ability' => 'checklists.edit'],
        ['name' => 'Checklist Tela de cadastro', 'ability' => 'checklists.createChecklist'],

    ];
}
