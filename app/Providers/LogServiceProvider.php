<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Log\LogManager;
use App\Services\Log\PermissionAwareLogger;

class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Configurar custom logger que garante permissões corretas
        $this->app->make(LogManager::class)->extend('permission_aware', function ($app, $config) {
            return (new PermissionAwareLogger())($config);
        });

        // Hook no evento de criação de arquivo de log para garantir permissões
        if (class_exists(\Monolog\Handler\RotatingFileHandler::class)) {
            $this->setupLogPermissionsHook();
        }
    }

    private function setupLogPermissionsHook(): void
    {
        // Garantir que novos arquivos de log sejam criados com permissões corretas
        $logPath = storage_path('logs');
        
        if (is_dir($logPath)) {
            // Garantir permissões do diretório
            chmod($logPath, 0775);
            
            // Hook para corrigir permissões de novos arquivos
            register_shutdown_function(function () use ($logPath) {
                $this->fixLogPermissions($logPath);
            });
        }
    }

    private function fixLogPermissions(string $logPath): void
    {
        $files = glob($logPath . '/laravel-*.log');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $currentPerms = fileperms($file) & 0777;
                
                // Se não tem as permissões corretas, corrigir
                if ($currentPerms !== 0664) {
                    chmod($file, 0664);
                }
                
                // Tentar corrigir owner se possível
                $stat = stat($file);
                if ($stat && function_exists('posix_getpwuid')) {
                    $fileOwner = posix_getpwuid($stat['uid']);
                    if ($fileOwner && $fileOwner['name'] !== 'www-data') {
                        try {
                            chown($file, 'www-data');
                            chgrp($file, 'www-data');
                        } catch (\Throwable $e) {
                            // Ignorar erro se não conseguir alterar owner
                        }
                    }
                }
            }
        }
    }
}