<?php

namespace App\Observers;

use App\Models\Checklist;
use App\Services\Logging\ChecklistLogger;

class ChecklistObserver
{
    protected $checklistLogger;

    /**
     * ChecklistObserver constructor.
     * Inject ChecklistLogger for centralized logging.
     */
    public function __construct(ChecklistLogger $checklistLogger)
    {
        $this->checklistLogger = $checklistLogger;
    }

    /**
     * Handle the Checklist "created" event.
     *
     * @param Checklist $checklist
     * @return void
     */
    public function created(Checklist $checklist)
    {
        // Observer logs at model level - controller logs business operations
        $additionalContext = [
            'source' => 'observer',
        ];

        // Check if it's a retroactive checklist
        if ($checklist->retroactive || ($checklist->created_at && !$checklist->created_at->isToday())) {
            $this->checklistLogger->retroactiveCreated($checklist, $additionalContext);
        } else {
            $this->checklistLogger->created($checklist, $additionalContext);
        }
    }

    /**
     * Handle the Checklist "updated" event.
     *
     * @param Checklist $checklist
     * @return void
     */
    public function updated(Checklist $checklist)
    {
        // Get the changed attributes
        $changes = [];
        foreach ($checklist->getDirty() as $field => $newValue) {
            $changes[$field] = [
                'old' => $checklist->getOriginal($field),
                'new' => $newValue,
            ];
        }

        // Only log if there are actual changes
        if (!empty($changes)) {
            $this->checklistLogger->updated($checklist, $changes, [
                'source' => 'observer',
            ]);
        }
    }

    /**
     * Handle the Checklist "deleted" event.
     *
     * @param Checklist $checklist
     * @return void
     */
    public function deleted(Checklist $checklist)
    {
        $this->checklistLogger->deleted($checklist, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Checklist "restored" event.
     *
     * @param Checklist $checklist
     * @return void
     */
    public function restored(Checklist $checklist)
    {
        $this->checklistLogger->restored($checklist, [
            'source' => 'observer',
        ]);
    }

    /**
     * Handle the Checklist "force deleted" event.
     * This is a permanent deletion and should be logged carefully.
     *
     * @param Checklist $checklist
     * @return void
     */
    public function forceDeleted(Checklist $checklist)
    {
        // Force delete is a critical operation - use alert level
        $this->checklistLogger->forceDeleted($checklist, [
            'source' => 'observer',
        ]);
    }
}
