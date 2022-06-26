<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role1 = Role::create([
            'name' => 'Super Admin',
            'role' => 'ROLE_SUPER_ADMIN',
            'created_by' => 1,
        ]);

        $role2 = Role::create([
            'name' => 'Admin',
            'role' => 'ROLE_ADMIN',
            'created_by' => 1,
        ]);
//
//        $role3 = Role::create([
//            'name' => 'Gestor',
//            'role' => 'ROLE_MANAGER',
//            'created_by' => 2,
//        ]);
//
//        $role4 = Role::create([
//            'name' => 'Profissional',
//            'role' => 'ROLE_PROFESSION',
//            'created_by' => 2,
//        ]);
//
//        $role5 = Role::create([
//            'name' => 'Pais',
//            'role' => 'ROLE_PARENT',
//            'created_by' => 2,
//        ]);

        foreach (Resources::RESOURCES as $resource) {
            Resource::create($resource)->roles()->sync([2]);
        }

//
//        Resource::create(['name' => 'Listar crianças', 'ability' => 'kids.index'])->roles()->sync([2, 3, 4, 5, 6]);
//        Resource::create(['name' => 'Excluir criança', 'ability' => 'kids.destroy'])->roles()->sync([2]);
//        Resource::create(['name' => 'Visualizar dados da criança', 'ability' => 'kids.show'])->roles()->sync([2, 3, 4, 5, 6]);
//        Resource::create(['name' => 'Cadastrar criança', 'ability' => 'kids.store'])->roles()->sync([2, 3, 4]);
//        Resource::create(['name' => 'Atualiar criança', 'ability' => 'kids.update'])->roles()->sync([2, 3, 4, 5]);
//        Resource::create(['name' => 'Tela de cadastro de criança', 'ability' => 'kids.create'])->roles()->sync([2, 3, 4]);
//        Resource::create(['name' => 'Tela de atualização criança', 'ability' => 'kids.edit'])->roles()->sync([2, 3, 4, 5]);
//
//        Resource::create(['name' => 'Listar usuários', 'ability' => 'users.index'])->roles()->sync([2]);
//        Resource::create(['name' => 'Excluir usuário', 'ability' => 'users.destroy'])->roles()->sync([2]);
//        Resource::create(['name' => 'Visualizar dados do usuário', 'ability' => 'users.show'])->roles()->sync([2]);
//        Resource::create(['name' => 'Cadastrar usuário', 'ability' => 'users.store'])->roles()->sync([2]);
//        Resource::create(['name' => 'Atualizar usuário', 'ability' => 'users.update'])->roles()->sync([2]);
//        Resource::create(['name' => 'Tela de cadastro de usuário', 'ability' => 'users.create'])->roles()->sync([2]);
//        Resource::create(['name' => 'Tela de atualização de usuarios', 'ability' => 'users.edit'])->roles()->sync([2]);
//
//        Resource::create(['name' => 'Listar papeis', 'ability' => 'roles.index'])->roles()->sync([2]);
//        Resource::create(['name' => 'Excluir papél', 'ability' => 'roles.destroy'])->roles()->sync([2]);
//        Resource::create(['name' => 'Exibir dados do papél', 'ability' => 'roles.show'])->roles()->sync([2]);
//        Resource::create(['name' => 'Cadastrar papél', 'ability' => 'roles.store'])->roles()->sync([2]);
//        Resource::create(['name' => 'Atualizar papél', 'ability' => 'roles.update'])->roles()->sync([2]);
//        Resource::create(['name' => 'Tela de recursos de papél', 'ability' => 'roles.resources'])->roles()->sync([2]);
//        Resource::create(['name' => 'Atualizar recursos do papél', 'ability' => 'roles.resources.update'])->roles()->sync([2]);
//        Resource::create(['name' => 'Tela de cadastro de papél', 'ability' => 'roles.create'])->roles()->sync([2]);
//        Resource::create(['name' => 'Tela de atualização de papél', 'ability' => 'roles.edit'])->roles()->sync([2]);
    }
}
