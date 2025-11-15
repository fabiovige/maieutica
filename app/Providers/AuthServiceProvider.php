<?php

namespace App\Providers;

use App\Models\Checklist;
use App\Models\Competence;
use App\Models\DocumentTemplate;
use App\Models\GeneratedDocument;
use App\Models\Kid;
use App\Models\Plane;
use App\Models\User;
use App\Policies\ChecklistPolicy;
use App\Policies\CompetencePolicy;
use App\Policies\DocumentTemplatePolicy;
use App\Policies\GeneratedDocumentPolicy;
use App\Policies\KidPolicy;
use App\Policies\PlanePolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Kid::class => KidPolicy::class,
        User::class => UserPolicy::class,
        Checklist::class => ChecklistPolicy::class,
        Competence::class => CompetencePolicy::class,
        Plane::class => PlanePolicy::class,
        Role::class => RolePolicy::class,
        DocumentTemplate::class => DocumentTemplatePolicy::class,
        GeneratedDocument::class => GeneratedDocumentPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
