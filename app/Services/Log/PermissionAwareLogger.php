<?php

declare(strict_types=1);

namespace App\Services\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

class PermissionAwareLogger
{
    public function __invoke(array $config)
    {
        $handler = new StreamHandler(
            $config['path'],
            $config['level'] ?? Logger::DEBUG
        );

        $handler->setFormatter(new LineFormatter(
            null,
            null,
            true,
            true
        ));

        $logger = new Logger($config['name'] ?? 'laravel');
        $logger->pushHandler($handler);

        // Garantir permissões corretas no arquivo de log
        $this->ensureCorrectPermissions($config['path']);

        return $logger;
    }

    private function ensureCorrectPermissions(string $logPath): void
    {
        if (file_exists($logPath)) {
            // Se o arquivo já existe, corrigir suas permissões
            chmod($logPath, 0664);

            // Tentar corrigir o owner se possível (pode falhar se não for root)
            try {
                $webUser = 'www-data';
                if (function_exists('posix_getpwnam')) {
                    $userInfo = posix_getpwnam($webUser);
                    if ($userInfo !== false) {
                        chown($logPath, $webUser);
                        chgrp($logPath, $webUser);
                    }
                }
            } catch (\Throwable $e) {
                // Ignorar erros de permissão - umask deve resolver
            }
        }

        // Garantir que o diretório pai tenha permissões corretas
        $logDir = dirname($logPath);
        if (is_dir($logDir)) {
            chmod($logDir, 0775);
        }
    }
}
