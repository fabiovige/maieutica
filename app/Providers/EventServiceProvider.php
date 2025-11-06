<?php

namespace App\Providers;

use App\Models\Checklist;
use App\Models\Kid;
use App\Models\Professional;
use App\Models\Role;
use App\Models\User;
use App\Observers\ChecklistObserver;
use App\Observers\KidObserver;
use App\Observers\ProfessionalObserver;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $observers = [
        Checklist::class => [ChecklistObserver::class],
        Kid::class => [KidObserver::class],
        Professional::class => [ProfessionalObserver::class],
        Role::class => [RoleObserver::class],
    ];

    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Checklist::observe(ChecklistObserver::class);
        Kid::observe(KidObserver::class);
        Professional::observe(ProfessionalObserver::class);
        Role::observe(RoleObserver::class);
        User::observe(UserObserver::class);
    }
}
