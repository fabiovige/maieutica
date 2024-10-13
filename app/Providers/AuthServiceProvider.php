<?php

namespace App\Providers;

use App\Models\Ability;
use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Kid;
use App\Models\Responsible;
use App\Models\User;
use App\Policies\ChecklistPolicy;
use App\Policies\CompetencePolicy;
use App\Policies\KidPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Kid::class => KidPolicy::class,
        User::class => UserPolicy::class,
        Checklist::class => ChecklistPolicy::class,
        Competence::class => CompetencePolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
