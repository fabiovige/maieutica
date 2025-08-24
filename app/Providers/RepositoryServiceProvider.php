<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\KidRepositoryInterface;
use App\Contracts\ProfessionalRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\Kid;
use App\Models\Professional;
use App\Models\User;
use App\Repositories\KidRepository;
use App\Repositories\ProfessionalRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KidRepositoryInterface::class, function ($app) {
            return new KidRepository(new Kid());
        });

        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository(new User());
        });

        $this->app->bind(ProfessionalRepositoryInterface::class, function ($app) {
            return new ProfessionalRepository(new Professional());
        });

        // Caso queira registrar outros repositórios no futuro
        // $this->app->bind(ChecklistRepositoryInterface::class, ChecklistRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
