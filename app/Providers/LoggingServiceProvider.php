<?php

namespace App\Providers;

use App\Services\Log\LoggingService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Log\Events\MessageLogged;

class LoggingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoggingService::class);
        
        $this->app->alias(LoggingService::class, 'logging-service');
    }

    public function boot(): void
    {
        $this->configureLogChannels();
        $this->registerLogEventListeners();
        $this->configureLogViewer();
    }

    private function configureLogChannels(): void
    {
        $channels = config('logging.channels');
        
        foreach (['application', 'security', 'performance', 'errors'] as $channel) {
            if (!isset($channels[$channel])) {
                config(["logging.channels.{$channel}" => [
                    'driver' => 'daily',
                    'path' => storage_path("logs/{$channel}.log"),
                    'level' => $this->getChannelLevel($channel),
                    'days' => $this->getChannelRetentionDays($channel),
                    'permission' => 0664,
                ]]);
            }
        }
    }

    private function registerLogEventListeners(): void
    {
        $this->app['events']->listen(MessageLogged::class, function (MessageLogged $event) {
            if ($this->shouldEnrichLogEntry($event)) {
                $this->enrichLogEntry($event);
            }
        });
    }

    private function configureLogViewer(): void
    {
        if (!$this->app->environment('testing')) {
            $this->publishes([
                __DIR__ . '/../../config/log-viewer.php' => config_path('log-viewer.php'),
            ], 'log-viewer-config');
        }
    }

    private function getChannelLevel(string $channel): string
    {
        return match($channel) {
            'security' => 'warning',
            'errors' => 'error',
            'performance', 'application' => 'info',
            default => 'debug'
        };
    }

    private function getChannelRetentionDays(string $channel): int
    {
        return match($channel) {
            'security', 'errors' => 90,
            'application' => 60,
            'performance' => 30,
            default => 30
        };
    }

    private function shouldEnrichLogEntry(MessageLogged $event): bool
    {
        return in_array($event->level, ['error', 'critical', 'alert', 'emergency']) ||
               str_contains($event->context['channel'] ?? '', 'security');
    }

    private function enrichLogEntry(MessageLogged $event): void
    {
        if (!isset($event->context['enriched'])) {
            $event->context['enriched'] = true;
            $event->context['environment'] = $this->app->environment();
            $event->context['application'] = config('app.name');
            $event->context['version'] = config('app.version', '1.0.0');
            
            if ($event->level === 'error' && isset($event->context['exception'])) {
                $event->context['error_hash'] = md5($event->message . ($event->context['exception']->getFile() ?? ''));
            }
        }
    }
}