<?php

namespace App\Modules\Lgpd\Providers;

use Illuminate\Support\ServiceProvider;

class LgpdServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/lgpd.php',
            'lgpd'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Infrastructure/Migrations');

        $this->loadRoutesFrom(__DIR__ . '/../Http/Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../Http/Routes/api.php');
    }
}
