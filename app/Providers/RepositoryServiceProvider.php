<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\KidRepositoryInterface;
use App\Models\Kid;
use App\Repositories\KidRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(KidRepositoryInterface::class, function ($app) {
            return new KidRepository(new Kid());
        });

        // Caso queira registrar outros repositórios no futuro
        // $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        // $this->app->bind(ChecklistRepositoryInterface::class, ChecklistRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
