<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\KidRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\Kid;
use App\Models\User;
use App\Repositories\KidRepository;
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

        // Caso queira registrar outros repositórios no futuro
        // $this->app->bind(ChecklistRepositoryInterface::class, ChecklistRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
