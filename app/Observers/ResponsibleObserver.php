<?php

namespace App\Observers;

use App\Models\Responsible;

class ResponsibleObserver
{
    /**
     * Handle the Responsible "created" event.
     *
     * @param  \App\Models\Responsible  $responsible
     * @return void
     */
    public function created(Responsible $responsible)
    {
        //
    }

    /**
     * Handle the Responsible "updated" event.
     *
     * @param  \App\Models\Responsible  $responsible
     * @return void
     */
    public function updated(Responsible $responsible)
    {
        //
    }

    /**
     * Handle the Responsible "deleted" event.
     *
     * @param  \App\Models\Responsible  $responsible
     * @return void
     */
    public function deleted(Responsible $responsible)
    {
        //
    }

    /**
     * Handle the Responsible "restored" event.
     *
     * @param  \App\Models\Responsible  $responsible
     * @return void
     */
    public function restored(Responsible $responsible)
    {
        //
    }

    /**
     * Handle the Responsible "force deleted" event.
     *
     * @param  \App\Models\Responsible  $responsible
     * @return void
     */
    public function forceDeleted(Responsible $responsible)
    {
        //
    }
}