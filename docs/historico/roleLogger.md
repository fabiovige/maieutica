# RoleLogger Documentation

## Overview

`RoleLogger` is a centralized logging service for all Role (Perfil) operations in the Maiêutica application. It provides structured, consistent logging with contextual information for debugging, security auditing, and compliance.

**Location**: `app/Services/Logging/RoleLogger.php`

**Related Components**:
- `app/Observers/RoleObserver.php` - Automatic model-level logging
- `app/Http/Controllers/RoleController.php` - Business-level logging integration
- `app/Models/Role.php` - Role model (Spatie Laravel Permission)

## Architecture

### Two-Layer Logging Strategy

```
┌─────────────────────────────────────────────────────────────┐
│                    RoleController                            │
│  (Business Logic Layer)                                      │
│  - Logs business operations (create, update, delete)         │
│  - Tracks changes with old/new values                        │
│  - Logs permission synchronization                           │
│  - Logs user assignments/removals                            │
│  - Logs access control decisions                             │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ uses
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                   RoleLogger Service                         │
│  (Centralized Logging)                                       │
│  - 14 specialized logging methods                            │
│  - Automatic user context injection                          │
│  - Structured log formatting                                 │
└────────────────────┬────────────────────────────────────────┘
                     │
                     │ used by
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    RoleObserver                              │
│  (Model Event Layer)                                         │
│  - Automatic logging on model events                         │
│  - created, updated, deleted, restored, forceDeleted         │
│  - Detects changes automatically via getDirty()              │
└─────────────────────────────────────────────────────────────┘
```

### Why Two Layers?

1. **Observer Layer** - Catches ALL model changes automatically (including Artisan commands, seeders, external packages)
2. **Controller Layer** - Provides business context (permission changes, user assignments, validation results)

Both layers work together to provide complete audit trail.

## Method Reference

### Lifecycle Methods

#### `created(Role $role, array $additionalContext = []): void`

**Purpose**: Log when a role is created.

**Log Level**: `NOTICE`

**Message**: "Role (perfil) criado"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'guard_name' => 'web',
    'source' => 'controller',
    'permissions_count' => 5,
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@store
$role = Role::create(['name' => $data['name']]);

$this->roleLogger->created($role, [
    'source' => 'controller',
    'permissions_count' => count($permissions),
]);
```

**When Called**:
- Controller: After creating role and syncing initial permissions
- Observer: Automatically on `created` event

---

#### `updated(Role $role, array $changes = [], array $additionalContext = []): void`

**Purpose**: Log when a role is updated with detailed change tracking.

**Log Level**: `NOTICE`

**Message**: "Role (perfil) atualizado"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Senior Editor',
    'changed_fields' => ['name'],
    'changes' => [
        'name' => [
            'old' => 'Editor',
            'new' => 'Senior Editor',
        ],
    ],
    'source' => 'controller',
    'permissions_changed' => true,
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@update
$originalData = $role->only(['name']);
$role->update(['name' => $data['name']]);

$changes = [];
$newData = $role->only(['name']);
foreach ($newData as $key => $value) {
    if ($originalData[$key] != $value) {
        $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
    }
}

if (!empty($changes)) {
    $this->roleLogger->updated($role, $changes, [
        'source' => 'controller',
        'permissions_changed' => $oldPermissions != $newPermissions,
    ]);
}
```

**When Called**:
- Controller: After updating role data with change tracking
- Observer: Automatically on `updated` event (uses `getDirty()`)

---

#### `deleted(Role $role, array $additionalContext = []): void`

**Purpose**: Log when a role is soft deleted (moved to trash).

**Log Level**: `NOTICE`

**Message**: "Role (perfil) movido para lixeira"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'source' => 'controller',
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@destroy
$role->delete();

$this->roleLogger->deleted($role, [
    'source' => 'controller',
]);
```

**When Called**:
- Controller: After soft deleting role
- Observer: Automatically on `deleted` event

---

#### `restored(Role $role, array $additionalContext = []): void`

**Purpose**: Log when a role is restored from trash.

**Log Level**: `NOTICE`

**Message**: "Role (perfil) restaurado da lixeira"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'source' => 'controller',
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@restore
$role->restore();

$this->roleLogger->restored($role, [
    'source' => 'controller',
]);
```

**When Called**:
- Controller: After restoring role from trash
- Observer: Automatically on `restored` event

---

#### `forceDeleted(Role $role, array $additionalContext = []): void`

**Purpose**: Log when a role is permanently deleted from database.

**Log Level**: `ALERT` (highest level - critical operation)

**Message**: "Role (perfil) excluído permanentemente"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'warning' => 'Role permanentemente deletado do banco de dados',
    'source' => 'observer',
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// Rarely used - only in admin contexts
$role->forceDelete();

// Observer automatically logs this
```

**When Called**:
- Observer: Automatically on `forceDeleted` event
- ⚠️ **Warning**: This is a destructive operation and should be logged carefully

---

### Permission Management Methods

#### `permissionAssigned(Role $role, string $permissionName, array $additionalContext = []): void`

**Purpose**: Log when a single permission is assigned to a role.

**Log Level**: `NOTICE`

**Message**: "Permissão atribuída ao role"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'permission_name' => 'post-edit',
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// When assigning a single permission
$role->givePermissionTo('post-edit');

$this->roleLogger->permissionAssigned($role, 'post-edit', [
    'source' => 'controller',
]);
```

**When Called**:
- Manual permission assignment operations
- One-off permission grants

---

#### `permissionRemoved(Role $role, string $permissionName, array $additionalContext = []): void`

**Purpose**: Log when a single permission is removed from a role.

**Log Level**: `NOTICE`

**Message**: "Permissão removida do role"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'permission_name' => 'post-delete',
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// When removing a single permission
$role->revokePermissionTo('post-delete');

$this->roleLogger->permissionRemoved($role, 'post-delete', [
    'source' => 'controller',
    'reason' => 'Security policy change',
]);
```

**When Called**:
- Manual permission revocation operations
- Security policy changes

---

#### `permissionsSynced(Role $role, array $oldPermissions, array $newPermissions, array $additionalContext = []): void`

**Purpose**: Log when permissions are synchronized (bulk update) with detailed diff.

**Log Level**: `NOTICE`

**Message**: "Permissões sincronizadas no role"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'old_permissions_count' => 8,
    'new_permissions_count' => 10,
    'added_permissions' => ['post-publish', 'post-schedule'],
    'removed_permissions' => [],
    'source' => 'controller',
    'on_creation' => false,
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@update
$oldPermissions = $role->permissions->pluck('name')->toArray();
$newPermissions = $request->input('permissions') ?? [];

$role->syncPermissions($newPermissions);

if ($oldPermissions != $newPermissions) {
    $this->roleLogger->permissionsSynced($role, $oldPermissions, $newPermissions, [
        'source' => 'controller',
    ]);
}
```

**When Called**:
- Controller: On role create (with empty old permissions)
- Controller: On role update (with permission changes)
- Automatically calculates added/removed permissions

---

### User Assignment Methods

#### `userAssigned(Role $role, int $userId, array $additionalContext = []): void`

**Purpose**: Log when a user is assigned to a role.

**Log Level**: `NOTICE`

**Message**: "Usuário atribuído ao role"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'user_id' => 123,
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In UserController or RoleController
$user->assignRole($role);

$this->roleLogger->userAssigned($role, $user->id, [
    'source' => 'controller',
    'previous_roles' => $user->roles->pluck('name')->toArray(),
]);
```

**When Called**:
- When assigning a role to a user
- Bulk user-role assignments

---

#### `userRemoved(Role $role, int $userId, array $additionalContext = []): void`

**Purpose**: Log when a user is removed from a role.

**Log Level**: `NOTICE`

**Message**: "Usuário removido do role"

**Context**:
```php
[
    'role_id' => 42,
    'role_name' => 'Editor',
    'user_id' => 123,
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In UserController or RoleController
$user->removeRole($role);

$this->roleLogger->userRemoved($role, $user->id, [
    'source' => 'controller',
    'reason' => 'Role reassignment',
]);
```

**When Called**:
- When removing a role from a user
- Bulk user-role removals

---

### View Methods

#### `viewed(Role $role, string $viewType = 'details', array $additionalContext = []): void`

**Purpose**: Log when a role is viewed (details or edit page).

**Log Level**: `INFO`

**Message**: "Role (perfil) visualizado"

**Context**:
```php
[
    'viewed_role_id' => 42,
    'role_name' => 'Editor',
    'view_type' => 'edit',
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@show
$this->roleLogger->viewed($role, 'details');

// In RoleController@edit
$this->roleLogger->viewed($role, 'edit');
```

**When Called**:
- RoleController@show (view_type: 'details')
- RoleController@edit (view_type: 'edit')

---

#### `listed(array $filters = [], array $additionalContext = []): void`

**Purpose**: Log when the roles index/list page is accessed.

**Log Level**: `DEBUG`

**Message**: "Lista de roles (perfis) acessada"

**Context**:
```php
[
    'filters' => [
        'search' => 'editor',
        'total_results' => 3,
    ],
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@index
$roles = $query->paginate(5);

$this->roleLogger->listed([
    'search' => $request->input('search'),
    'total_results' => $roles->total(),
]);
```

**When Called**:
- RoleController@index

---

#### `trashViewed(array $additionalContext = []): void`

**Purpose**: Log when the trash/deleted roles list is accessed.

**Log Level**: `INFO`

**Message**: "Lixeira de roles (perfis) visualizada"

**Context**:
```php
[
    'total_trashed' => 5,
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@trash
$roles = Role::onlyTrashed()->paginate(5);

$this->roleLogger->trashViewed([
    'total_trashed' => $roles->total(),
]);
```

**When Called**:
- RoleController@trash

---

### Error Handling Methods

#### `operationFailed(string $operation, \Exception $exception, array $additionalContext = []): void`

**Purpose**: Log when a role operation fails with full exception details.

**Log Level**: `ERROR`

**Message**: "Operação de role (perfil) falhou: {operation}"

**Context**:
```php
[
    'operation' => 'update',
    'error' => 'Database connection error',
    'exception_class' => 'PDOException',
    'file' => '/var/www/app/Http/Controllers/RoleController.php',
    'line' => 156,
    'role_id' => 42,
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController (catch blocks)
try {
    // ... operation
} catch (\Exception $e) {
    $this->roleLogger->operationFailed('update', $e, [
        'role_id' => $id,
    ]);

    flash(self::MSG_UPDATE_ERROR)->error();
    return redirect()->back();
}
```

**When Called**:
- All catch blocks in RoleController
- Any failed role operation

---

#### `accessDenied(string $operation, ?Role $role = null, array $additionalContext = []): void`

**Purpose**: Log when access is denied to a role operation (authorization failure).

**Log Level**: `WARNING`

**Message**: "Acesso negado à operação de role (perfil)"

**Context**:
```php
[
    'operation' => 'delete',
    'target_role_id' => 42,
    'role_name' => 'Editor',
    'reason' => 'Role tem usuários vinculados',
    'users_count' => 5,
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]
```

**Usage Example**:
```php
// In RoleController@destroy
$usersCount = $role->users()->count();
if ($usersCount > 0) {
    $this->roleLogger->accessDenied('delete', $role, [
        'reason' => 'Role tem usuários vinculados',
        'users_count' => $usersCount,
    ]);

    throw new \Exception('Não é possível mover para lixeira...');
}
```

**When Called**:
- When a role cannot be deleted due to linked users
- Authorization failures (from policies)
- Business rule violations

---

## Helper Methods

### `buildUserContext(): array`

**Purpose**: Build user context for logging (actor information).

**Returns**:
```php
// Authenticated user
[
    'actor_user_id' => 1,
    'actor_user_name' => 'Admin User',
    'actor_user_email' => 'admin@example.com',
    'ip' => '192.168.1.100',
]

// Guest (unauthenticated)
[
    'actor_user_id' => null,
    'actor_user_name' => 'Guest',
    'ip' => '192.168.1.100',
]
```

**When Used**: Automatically called by all logging methods to add actor context.

---

## Integration Examples

### RoleController Constructor

```php
class RoleController extends Controller
{
    protected $roleLogger;

    public function __construct(RoleLogger $roleLogger)
    {
        $this->roleLogger = $roleLogger;
    }
}
```

### Create Operation with Permission Sync

```php
public function store(RoleRequest $request)
{
    $this->authorize('create', Role::class);

    DB::beginTransaction();
    try {
        $role = Role::create(['name' => $request->name]);

        $permissions = [];
        if ($request->has('permissions')) {
            $permissions = $request->input('permissions');
            $role->syncPermissions($permissions);

            // Log permissions sync
            $this->roleLogger->permissionsSynced($role, [], $permissions, [
                'source' => 'controller',
                'on_creation' => true,
            ]);
        }

        // Observer will log at model level
        $this->roleLogger->created($role, [
            'source' => 'controller',
            'permissions_count' => count($permissions),
        ]);

        DB::commit();
        flash(self::MSG_CREATE_SUCCESS)->success();

        return redirect()->route('roles.index');
    } catch (\Exception $e) {
        DB::rollBack();

        $this->roleLogger->operationFailed('store', $e);

        flash(self::MSG_CREATE_ERROR)->error();
        return redirect()->back()->withInput();
    }
}
```

### Update Operation with Change Tracking

```php
public function update(RoleRequest $request, $id)
{
    $role = Role::findOrFail($id);
    $this->authorize('update', $role);

    DB::beginTransaction();
    try {
        // Get original data for change tracking
        $originalData = $role->only(['name']);
        $oldPermissions = $role->permissions->pluck('name')->toArray();

        // Update role
        $role->update(['name' => $request->name]);

        // Sync permissions
        $newPermissions = $request->input('permissions') ?? [];
        $role->syncPermissions($newPermissions);

        // Track what changed
        $changes = [];
        $newData = $role->only(['name']);
        foreach ($newData as $key => $value) {
            if ($originalData[$key] != $value) {
                $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
            }
        }

        // Log permissions sync
        if ($oldPermissions != $newPermissions) {
            $this->roleLogger->permissionsSynced($role, $oldPermissions, $newPermissions, [
                'source' => 'controller',
            ]);
        }

        // Observer will log at model level
        if (!empty($changes)) {
            $this->roleLogger->updated($role, $changes, [
                'source' => 'controller',
                'permissions_changed' => $oldPermissions != $newPermissions,
            ]);
        }

        DB::commit();
        flash(self::MSG_UPDATE_SUCCESS)->success();

        return redirect()->route('roles.edit', $id);
    } catch (\Exception $e) {
        DB::rollBack();

        $this->roleLogger->operationFailed('update', $e, [
            'role_id' => $id,
        ]);

        flash(self::MSG_UPDATE_ERROR)->error();
        return redirect()->back();
    }
}
```

### Delete Operation with Validation

```php
public function destroy(Role $role)
{
    $this->authorize('delete', $role);

    DB::beginTransaction();

    try {
        // Validate role can be deleted
        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            $this->roleLogger->accessDenied('delete', $role, [
                'reason' => 'Role tem usuários vinculados',
                'users_count' => $usersCount,
            ]);

            throw new \Exception('Não é possível mover para lixeira, pois existem usuários vinculados a este perfil.');
        }

        $role->delete();

        // Observer will log at model level
        $this->roleLogger->deleted($role, [
            'source' => 'controller',
        ]);

        DB::commit();
        flash('Perfil movido para a lixeira com sucesso.')->success();

        return redirect()->route('roles.index');
    } catch (\Exception $e) {
        DB::rollBack();
        flash($e->getMessage())->error();

        $this->roleLogger->operationFailed('destroy', $e, [
            'role_id' => $role->id,
        ]);

        return redirect()->back();
    }
}
```

---

## RoleObserver Integration

### Observer Methods

```php
namespace App\Observers;

use App\Models\Role;
use App\Services\Logging\RoleLogger;

class RoleObserver
{
    protected $roleLogger;

    public function __construct(RoleLogger $roleLogger)
    {
        $this->roleLogger = $roleLogger;
    }

    public function created(Role $role)
    {
        // Observer logs at model level
        $this->roleLogger->created($role, [
            'source' => 'observer',
        ]);
    }

    public function updated(Role $role)
    {
        // Get the changed attributes
        $changes = [];
        foreach ($role->getDirty() as $field => $newValue) {
            $changes[$field] = [
                'old' => $role->getOriginal($field),
                'new' => $newValue,
            ];
        }

        // Only log if there are actual changes
        if (!empty($changes)) {
            $this->roleLogger->updated($role, $changes, [
                'source' => 'observer',
            ]);
        }
    }

    public function deleted(Role $role)
    {
        $this->roleLogger->deleted($role, [
            'source' => 'observer',
        ]);
    }

    public function restored(Role $role)
    {
        $this->roleLogger->restored($role, [
            'source' => 'observer',
        ]);
    }

    public function forceDeleted(Role $role)
    {
        // Force delete is a critical operation - use alert level
        $this->roleLogger->forceDeleted($role, [
            'source' => 'observer',
        ]);
    }
}
```

### Observer Registration

```php
// In EventServiceProvider.php
protected $observers = [
    Role::class => [RoleObserver::class],
];

public function boot()
{
    Role::observe(RoleObserver::class);
}
```

---

## Log Level Guide

| Level | When to Use | Examples |
|-------|-------------|----------|
| `DEBUG` | List/index access | listed() |
| `INFO` | View operations, non-critical events | viewed(), trashViewed() |
| `NOTICE` | Normal but significant events | created(), updated(), deleted(), restored(), permissionsSynced(), userAssigned() |
| `WARNING` | Authorization failures, access denied | accessDenied() |
| `ERROR` | Operation failures, exceptions | operationFailed() |
| `ALERT` | Critical destructive operations | forceDeleted() |

---

## Comparison with Other Loggers

### RoleLogger vs UserLogger

| Feature | RoleLogger | UserLogger |
|---------|------------|------------|
| **Entity** | Role (Perfil) | User |
| **Primary Focus** | Permissions management | User lifecycle |
| **Unique Methods** | permissionsSynced(), userAssigned() | passwordChanged(), statusChanged() |
| **Change Tracking** | Role name, permissions | Name, email, password |
| **Security Focus** | Permission audit trail | Authentication events |

### RoleLogger vs ChecklistLogger

| Feature | RoleLogger | ChecklistLogger |
|---------|------------|----------------|
| **Entity** | Role (Perfil) | Checklist |
| **Primary Focus** | Authorization | Clinical assessments |
| **Unique Methods** | permissionsSynced(), userAssigned() | cloned(), competenceNoteUpdated() |
| **Privacy** | None | LGPD compliance (initials) |
| **Complexity** | Medium | High (18 methods) |

### RoleLogger vs ProfessionalLogger

| Feature | RoleLogger | ProfessionalLogger |
|---------|------------|-------------------|
| **Entity** | Role (Perfil) | Professional |
| **Primary Focus** | Permissions | Professional management |
| **Unique Methods** | permissionsSynced() | userLinked(), kidLinked(), activated() |
| **Relationships** | Many-to-many (users, permissions) | One-to-one (user), many-to-many (kids) |
| **Business Logic** | Permission validation | Specialty tracking, activation |

---

## Troubleshooting

### Logs Not Appearing

**Problem**: RoleLogger methods are called but no logs appear.

**Solutions**:
1. Check log configuration in `config/logging.php`
2. Verify log level threshold (DEBUG < INFO < NOTICE < WARNING < ERROR < ALERT)
3. Check file permissions on `storage/logs/laravel.log`
4. Verify RoleLogger is properly injected in constructor
5. Clear config cache: `php artisan config:clear`

### Observer Not Triggering

**Problem**: Observer events not being logged automatically.

**Solutions**:
1. Verify observer is registered in `EventServiceProvider.php`
2. Run `php artisan clear-compiled` and `composer dump-autoload`
3. Check if model is using `SoftDeletes` trait (required for `deleted`/`restored` events)
4. Verify observer constructor receives RoleLogger dependency

### Duplicate Logs

**Problem**: Seeing duplicate log entries for the same operation.

**Expected Behavior**: This is normal! Both Observer and Controller log the same event with different sources:
- Observer: `'source' => 'observer'` - Model-level event
- Controller: `'source' => 'controller'` - Business-level event

**Differentiation**:
```php
// Observer log
[
    'source' => 'observer',
    // Basic model data
]

// Controller log
[
    'source' => 'controller',
    'permissions_count' => 5,
    'permissions_changed' => true,
    // Business context
]
```

### Permission Sync Not Logged

**Problem**: Permissions are being synced but `permissionsSynced()` isn't called.

**Solutions**:
1. Verify you're comparing old vs new permissions:
```php
$oldPermissions = $role->permissions->pluck('name')->toArray();
// ... sync ...
$newPermissions = $request->input('permissions') ?? [];

if ($oldPermissions != $newPermissions) {
    $this->roleLogger->permissionsSynced($role, $oldPermissions, $newPermissions);
}
```
2. Ensure `syncPermissions()` is called BEFORE logging
3. Check that permissions are being passed correctly from request

### Missing User Context

**Problem**: Logs showing `actor_user_id: null` when user should be authenticated.

**Solutions**:
1. Verify user is authenticated: `auth()->check()`
2. Check middleware is applied to route (`auth` middleware)
3. Verify session configuration
4. For API routes, check token authentication

---

## Best Practices

### 1. Always Track Changes

```php
// ✅ GOOD - Track changes
$originalData = $role->only(['name']);
$role->update(['name' => $newName]);

$changes = [];
foreach ($role->only(['name']) as $key => $value) {
    if ($originalData[$key] != $value) {
        $changes[$key] = ['old' => $originalData[$key], 'new' => $value];
    }
}

$this->roleLogger->updated($role, $changes);

// ❌ BAD - No change tracking
$role->update(['name' => $newName]);
$this->roleLogger->updated($role);
```

### 2. Log Permission Changes

```php
// ✅ GOOD - Track permission changes
$oldPermissions = $role->permissions->pluck('name')->toArray();
$role->syncPermissions($newPermissions);

if ($oldPermissions != $newPermissions) {
    $this->roleLogger->permissionsSynced($role, $oldPermissions, $newPermissions);
}

// ❌ BAD - No permission tracking
$role->syncPermissions($newPermissions);
```

### 3. Add Context to Errors

```php
// ✅ GOOD - Include context
catch (\Exception $e) {
    $this->roleLogger->operationFailed('update', $e, [
        'role_id' => $id,
        'attempted_changes' => $request->all(),
    ]);
}

// ❌ BAD - No context
catch (\Exception $e) {
    $this->roleLogger->operationFailed('update', $e);
}
```

### 4. Use Transactions

```php
// ✅ GOOD - Wrap in transaction
DB::beginTransaction();
try {
    $role->update($data);
    $role->syncPermissions($permissions);

    $this->roleLogger->updated($role, $changes);

    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    $this->roleLogger->operationFailed('update', $e);
}
```

### 5. Log Access Denied

```php
// ✅ GOOD - Log why access was denied
$usersCount = $role->users()->count();
if ($usersCount > 0) {
    $this->roleLogger->accessDenied('delete', $role, [
        'reason' => 'Role tem usuários vinculados',
        'users_count' => $usersCount,
    ]);

    throw new \Exception('Cannot delete...');
}
```

---

## Security Considerations

### 1. Sensitive Data

RoleLogger does NOT log:
- User passwords
- Authentication tokens
- API keys

RoleLogger DOES log:
- Role names (public information)
- Permission names (authorization data)
- User IDs (for audit trail)
- IP addresses (security audit)

### 2. Audit Trail

All logs include:
- `actor_user_id` - Who performed the action
- `actor_user_name` - Human-readable identifier
- `actor_user_email` - Contact information
- `ip` - Request IP address
- Timestamp (automatic via Laravel Log facade)

### 3. Force Delete Alert

`forceDeleted()` uses `ALERT` level because:
- Permanent data loss
- Cannot be undone
- Requires immediate attention in monitoring systems

---

## Testing

### Manual Testing

```bash
# 1. Create a role
php artisan tinker
>>> $role = Role::create(['name' => 'Test Role']);

# 2. Check logs
tail -f storage/logs/laravel.log

# 3. Update role
>>> $role->update(['name' => 'Updated Role']);

# 4. Assign permissions
>>> $role->givePermissionTo('user-list');

# 5. Delete role
>>> $role->delete();

# 6. Restore role
>>> $role->restore();
```

### Unit Test Example

```php
use App\Services\Logging\RoleLogger;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class RoleLoggerTest extends TestCase
{
    public function test_logs_role_creation()
    {
        Log::shouldReceive('notice')
            ->once()
            ->with('Role (perfil) criado', Mockery::on(function ($context) {
                return isset($context['role_id'])
                    && isset($context['role_name'])
                    && isset($context['actor_user_id']);
            }));

        $roleLogger = new RoleLogger();
        $role = Role::factory()->create();

        $roleLogger->created($role);
    }

    public function test_logs_permission_sync()
    {
        Log::shouldReceive('notice')
            ->once()
            ->with('Permissões sincronizadas no role', Mockery::on(function ($context) {
                return isset($context['added_permissions'])
                    && isset($context['removed_permissions'])
                    && is_array($context['added_permissions']);
            }));

        $roleLogger = new RoleLogger();
        $role = Role::factory()->create();

        $roleLogger->permissionsSynced($role, ['old-perm'], ['new-perm']);
    }
}
```

---

## Summary

**RoleLogger** provides comprehensive logging for all Role (Perfil) operations with:
- ✅ **14 specialized methods** covering lifecycle, permissions, users, views, and errors
- ✅ **Two-layer logging** (Observer + Controller) for complete audit trail
- ✅ **Detailed permission tracking** with add/remove diffs
- ✅ **User assignment logging** for RBAC audit
- ✅ **Automatic user context** injection on all logs
- ✅ **Structured logging** with consistent format
- ✅ **Security audit trail** with IP addresses and actor information

This logging system ensures complete traceability of all role and permission changes for debugging, security auditing, and compliance purposes.
