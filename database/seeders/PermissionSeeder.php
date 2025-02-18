// Criar a permissão
Permission::create(['name' => 'view logs', 'guard_name' => 'web']);

// Atribuir ao role admin
$adminRole = Role::findByName('admin');
$adminRole->givePermissionTo('view logs');
