<?php

namespace App\Observers;

use App\Models\Kid;
use Illuminate\Support\Facades\Auth;

class KidObserver
{

    /**
     * Handle the Kid "created" event.
     *
     * @param  \App\Models\Kid  $kid
     * @return void
     */
    public function created(Kid $kid)
    {

    }

    /**
     * Handle the Kid "updated" event.
     *
     * @param  \App\Models\Kid  $kid
     * @return void
     */
    public function updated(Kid $kid)
    {

    }

    /**
     * Handle the Kid "deleted" event.
     *
     * @param  \App\Models\Kid  $kid
     * @return void
     */
    public function deleted(Kid $kid)
    {

    }

    /**
     * Handle the Kid "restored" event.
     *
     * @param  \App\Models\Kid  $kid
     * @return void
     */
    public function restored(Kid $kid)
    {
        //
    }

    /**
     * Handle the Kid "force deleted" event.
     *
     * @param  \App\Models\Kid  $kid
     * @return void
     */
    public function forceDeleted(Kid $kid)
    {
        //
    }
}
