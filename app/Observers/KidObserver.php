<?php

namespace App\Observers;

use App\Models\Kid;

class KidObserver
{
    /**
     * Handle the Kid "created" event.
     *
     * @return void
     */
    public function created(Kid $kid)
    {
    }

    /**
     * Handle the Kid "updated" event.
     *
     * @return void
     */
    public function updated(Kid $kid)
    {
        // dd('updated');
    }

    /**
     * Handle the Kid "deleted" event.
     *
     * @return void
     */
    public function deleted(Kid $kid)
    {
    }

    /**
     * Handle the Kid "restored" event.
     *
     * @return void
     */
    public function restored(Kid $kid)
    {
        //
    }

    /**
     * Handle the Kid "force deleted" event.
     *
     * @return void
     */
    public function forceDeleted(Kid $kid)
    {
        //
    }
}
