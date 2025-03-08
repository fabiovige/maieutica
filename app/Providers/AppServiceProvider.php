<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\View\Components\Address;
use App\View\Components\Button;
use App\View\Components\Table;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        Blade::component('button', Button::class);
        Blade::component('table', Table::class);
        Blade::component('address', Address::class);

        // Observers
        User::observe(UserObserver::class);

        // Configuração para resolver o problema de SSL com o reCAPTCHA
        \Illuminate\Support\Facades\Http::macro('noSslVerification', function () {
            return \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false,
            ]);
        });
    }
}
