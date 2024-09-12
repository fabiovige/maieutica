<?php

namespace App\Providers;

use App\Models\Ability;
use App\Models\Responsible;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Responsible::class => 'App\Policies\ResponsiblePolicy',
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

        // Verifica se as tabelas existem antes de executar qualquer operação no banco
        if (Schema::hasTable('abilities') && Schema::hasTable('roles')) {
            $abilities = Ability::all();  // Mova isso para dentro do bloco condicional
            //dd('Tabelas existem', $abilities);

            if ($abilities) {
                foreach ($abilities as $ability) {
                    Gate::define($ability->ability, function (User $user) use ($ability) {
                        return $ability->roles->contains($user->role);
                    });
                }
            }
        }
    }
}
