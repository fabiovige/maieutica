<?php

namespace App\Enums;

enum LogOperation: string
{
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case REGISTER = 'register';
    case RESET_PASSWORD = 'reset_password';
    case CHANGE_PASSWORD = 'change_password';
    case UPLOAD = 'upload';
    case DOWNLOAD = 'download';
    case EXPORT = 'export';
    case IMPORT = 'import';
    case SYNC = 'sync';
    case BACKUP = 'backup';
    case RESTORE = 'restore';
    case MIGRATE = 'migrate';
    case SEED = 'seed';
    case CACHE_CLEAR = 'cache_clear';
    case QUEUE_PROCESS = 'queue_process';
    case EMAIL_SEND = 'email_send';
    case PERMISSION_GRANT = 'permission_grant';
    case PERMISSION_REVOKE = 'permission_revoke';
    case ACCESS_DENIED = 'access_denied';
    case VALIDATION_FAILED = 'validation_failed';
    case EXCEPTION_OCCURRED = 'exception_occurred';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::CREATE => 'Criar',
            self::READ => 'Visualizar',
            self::UPDATE => 'Atualizar',
            self::DELETE => 'Excluir',
            self::LOGIN => 'Login',
            self::LOGOUT => 'Logout',
            self::REGISTER => 'Registrar',
            self::RESET_PASSWORD => 'Redefinir Senha',
            self::CHANGE_PASSWORD => 'Alterar Senha',
            self::UPLOAD => 'Upload',
            self::DOWNLOAD => 'Download',
            self::EXPORT => 'Exportar',
            self::IMPORT => 'Importar',
            self::SYNC => 'Sincronizar',
            self::BACKUP => 'Backup',
            self::RESTORE => 'Restaurar',
            self::MIGRATE => 'Migrar',
            self::SEED => 'Popular',
            self::CACHE_CLEAR => 'Limpar Cache',
            self::QUEUE_PROCESS => 'Processar Fila',
            self::EMAIL_SEND => 'Enviar Email',
            self::PERMISSION_GRANT => 'Conceder Permissão',
            self::PERMISSION_REVOKE => 'Revogar Permissão',
            self::ACCESS_DENIED => 'Acesso Negado',
            self::VALIDATION_FAILED => 'Validação Falhou',
            self::EXCEPTION_OCCURRED => 'Exceção Ocorrida',
        };
    }

    public function getLogLevel(): string
    {
        return match ($this) {
            self::LOGIN, self::LOGOUT, self::CREATE, self::UPDATE, self::READ => 'info',
            self::DELETE, self::RESET_PASSWORD, self::CHANGE_PASSWORD => 'warning',
            self::ACCESS_DENIED, self::VALIDATION_FAILED => 'error',
            self::EXCEPTION_OCCURRED => 'critical',
            default => 'info'
        };
    }
}
