<?php

namespace App\Observers;

use App\Models\Professional;
use App\Services\Logging\ProfessionalLogger;

class ProfessionalObserver
{
    protected $professionalLogger;

    /**
     * ProfessionalObserver constructor.
     * Inject ProfessionalLogger for centralized logging.
     */
    public function __construct(ProfessionalLogger $professionalLogger)
    {
        $this->professionalLogger = $professionalLogger;
    }

    /**
     * Handle the Professional "created" event.
     *
     * @param Professional $professional
     * @return void
     */
    public function created(Professional $professional)
    {
        // Observer logs at model level - controller logs business operations
        $this->professionalLogger->created($professional, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Professional "updated" event.
     *
     * @param Professional $professional
     * @return void
     */
    public function updated(Professional $professional)
    {
        // Get the changed attributes
        $changes = [];
        foreach ($professional->getDirty() as $field => $newValue) {
            $changes[$field] = [
                'old' => $professional->getOriginal($field),
                'new' => $newValue,
            ];
        }

        // Only log if there are actual changes
        if (!empty($changes)) {
            // Check if specialty changed
            if (isset($changes['specialty_id'])) {
                $this->professionalLogger->specialtyChanged(
                    $professional,
                    $changes['specialty_id']['old'],
                    $changes['specialty_id']['new'],
                    ['source' => 'observer']
                );
            }

            $this->professionalLogger->updated($professional, $changes, [
                'source' => 'observer',
            ]);
        }
    }

    /**
     * Handle the Professional "deleted" event.
     *
     * @param Professional $professional
     * @return void
     */
    public function deleted(Professional $professional)
    {
        $this->professionalLogger->deleted($professional, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Professional "restored" event.
     *
     * @param Professional $professional
     * @return void
     */
    public function restored(Professional $professional)
    {
        $this->professionalLogger->restored($professional, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Professional "force deleted" event.
     * This is a permanent deletion and should be logged carefully.
     *
     * @param Professional $professional
     * @return void
     */
    public function forceDeleted(Professional $professional)
    {
        // Force delete is a critical operation - use alert level
        $this->professionalLogger->forceDeleted($professional, [
            'source' => 'observer',
        ]);
    }
}
