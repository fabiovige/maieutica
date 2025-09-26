<?php

namespace App\Providers;

use App\Services\Security\LoginRateLimiterService;
use App\Services\Security\SecurityMonitoringService;
use App\Services\Security\SecurityTestService;
use App\Services\Backup\BackupService;
use App\Services\Lgpd\LgpdComplianceService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Services\Log\LoggingService;

class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar LoggingService como singleton se ainda não estiver
        $this->app->singleton(LoggingService::class, function ($app) {
            return new LoggingService();
        });

        $this->app->singleton(LoginRateLimiterService::class, function ($app) {
            return new LoginRateLimiterService(
                $app->make(RateLimiter::class),
                $app->make(LoggingService::class)
            );
        });

        $this->app->singleton(LgpdComplianceService::class, function ($app) {
            return new LgpdComplianceService();
        });

        $this->app->singleton(SecurityMonitoringService::class, function ($app) {
            return new SecurityMonitoringService(
                $app->make(LoggingService::class)
            );
        });

        $this->app->singleton(BackupService::class, function ($app) {
            return new BackupService(
                $app->make(LoggingService::class)
            );
        });

        $this->app->singleton(SecurityTestService::class, function ($app) {
            return new SecurityTestService(
                $app->make(LoggingService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}