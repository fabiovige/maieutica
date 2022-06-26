<?php

namespace App\Providers;

use App\Models\Resource;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (! Schema::hasTable('resources')) {
            return null;
        }

        Gate::before(function ($user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        $resources = Resource::all();

        foreach ($resources as $resource) {
            Gate::define($resource->ability, function ($user) use ($resource) {
                return $resource->roles->contains($user->role);
            });
        }
    }
}
