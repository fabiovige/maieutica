<?php

namespace App\Observers;

use App\Models\Role;
use App\Services\Logging\RoleLogger;

class RoleObserver
{
    protected $roleLogger;

    /**
     * RoleObserver constructor.
     * Inject RoleLogger for centralized logging.
     */
    public function __construct(RoleLogger $roleLogger)
    {
        $this->roleLogger = $roleLogger;
    }

    /**
     * Handle the Role "created" event.
     *
     * @param Role $role
     * @return void
     */
    public function created(Role $role)
    {
        // Observer logs at model level - controller logs business operations
        $this->roleLogger->created($role, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Role "updated" event.
     *
     * @param Role $role
     * @return void
     */
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

    /**
     * Handle the Role "deleted" event.
     *
     * @param Role $role
     * @return void
     */
    public function deleted(Role $role)
    {
        $this->roleLogger->deleted($role, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Role "restored" event.
     *
     * @param Role $role
     * @return void
     */
    public function restored(Role $role)
    {
        $this->roleLogger->restored($role, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Role "force deleted" event.
     * This is a permanent deletion and should be logged carefully.
     *
     * @param Role $role
     * @return void
     */
    public function forceDeleted(Role $role)
    {
        // Force delete is a critical operation - use alert level
        $this->roleLogger->forceDeleted($role, [
            'source' => 'observer',
        ]);
    }
}
