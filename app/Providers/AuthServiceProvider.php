<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Checklist;
use App\Models\Competence;
use App\Models\Kid;
use App\Models\Professional;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\ChecklistPolicy;
use App\Policies\CompetencePolicy;
use App\Policies\KidPolicy;
use App\Policies\ProfessionalPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AuditLog::class => AuditLogPolicy::class,
        Kid::class => KidPolicy::class,
        User::class => UserPolicy::class,
        Checklist::class => ChecklistPolicy::class,
        Competence::class => CompetencePolicy::class,
        Professional::class => ProfessionalPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
