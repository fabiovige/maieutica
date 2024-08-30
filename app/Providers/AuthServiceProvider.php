<?php

namespace App\Providers;

use App\Models\Ability;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [

    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                return true;
            } else {
                return null;
            }
        });

        $abilities = Ability::all();
        if ($abilities) {
            foreach ($abilities as $ability) {
                Gate::define($ability->ability, function (User $user) use ($ability) {
                    return $ability->roles->contains($user->role);
                });
            }
        }
    }
}
