<?php

namespace App\Jobs;

use App\Models\Kid;
use App\Models\User;
use App\Notifications\KidUpdateNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendKidUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Kid $kid;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Kid $kid)
    {
        $this->kid = $kid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $admin = User::where('id','=',2)->get();
        Notification::send($admin, new KidUpdateNotification($this->kid));
    }
}
