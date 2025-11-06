<?php

namespace App\Observers;

use App\Models\Kid;
use App\Services\Logging\KidLogger;

class KidObserver
{
    protected $kidLogger;

    /**
     * KidObserver constructor.
     * Inject KidLogger for centralized logging.
     */
    public function __construct(KidLogger $kidLogger)
    {
        $this->kidLogger = $kidLogger;
    }

    /**
     * Handle the Kid "created" event.
     *
     * @return void
     */
    public function created(Kid $kid)
    {
        // Observer logs at model level - controller logs business operations
        $this->kidLogger->created($kid, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Kid "updated" event.
     * Logs what fields changed at the model level.
     *
     * @return void
     */
    public function updated(Kid $kid)
    {
        // Get the changed attributes
        $changes = [];
        foreach ($kid->getDirty() as $field => $newValue) {
            $changes[$field] = [
                'old' => $kid->getOriginal($field),
                'new' => $newValue,
            ];
        }

        // Only log if there are actual changes
        if (!empty($changes)) {
            $this->kidLogger->updated($kid, $changes, [
                'source' => 'observer',
            ]);
        }
    }

    /**
     * Handle the Kid "deleted" event.
     *
     * @return void
     */
    public function deleted(Kid $kid)
    {
        $this->kidLogger->deleted($kid, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Kid "restored" event.
     *
     * @return void
     */
    public function restored(Kid $kid)
    {
        $this->kidLogger->restored($kid, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Kid "force deleted" event.
     * This is a permanent deletion and should be logged carefully.
     *
     * @return void
     */
    public function forceDeleted(Kid $kid)
    {
        // Force delete is a critical operation - use WARNING level
        // We'll log through the logger but note it's permanent deletion
        $this->kidLogger->deleted($kid, [
            'source' => 'observer',
            'permanent' => true,
            'warning' => 'Kid permanently deleted from database',
        ]);
    }
}
