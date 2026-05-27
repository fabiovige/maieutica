<?php

namespace App\Modules\Lgpd\Providers;

use App\Models\MedicalRecord;
use App\Modules\Lgpd\Application\Listeners\MedicalRecordAccessListener;
use App\Modules\Lgpd\Domain\Events\MedicalRecordAccessed;
use App\Modules\Lgpd\Infrastructure\Observers\MedicalRecordLgpdObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LgpdServiceProvider extends ServiceProvider
{
    /**
     * Mapeamento de eventos para listeners do módulo LGPD.
     *
     * MedicalRecordAccessed: disparado por controllers quando prontuário é
     * visualizado ou baixado em PDF (operações de leitura).
     *
     * Operações de escrita (edit, delete, restore) são capturadas pelo
     * MedicalRecordLgpdObserver registrado no boot().
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected array $listen = [
        MedicalRecordAccessed::class => [
            MedicalRecordAccessListener::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/lgpd.php',
            'lgpd'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Infrastructure/Migrations');

        $this->loadRoutesFrom(__DIR__.'/../Http/Routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../Http/Routes/api.php');

        $this->registerEventListeners();

        MedicalRecord::observe(MedicalRecordLgpdObserver::class);
    }

    /**
     * Registra os listeners de eventos do módulo LGPD.
     */
    protected function registerEventListeners(): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }
}
