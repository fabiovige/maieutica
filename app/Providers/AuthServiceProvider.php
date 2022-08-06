<?php

namespace App\Providers;

use App\Models\Ability;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot()
    {
        $this->registerPolicies();

        if (! Schema::hasTable('abilities')) {
            return null;
        }

        Gate::before(function (User $user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        $abilities = Ability::all();

        foreach ($abilities as $ability) {
            Gate::define($ability->ability, function (User $user) use ($ability) {
                return $ability->roles->contains($user->role);
            });
        }
    }
}
