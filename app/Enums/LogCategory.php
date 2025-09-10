<?php

namespace App\Enums;

enum LogCategory: string
{
    case USER_OPERATIONS = 'user_operations';
    case PROFESSIONAL_OPERATIONS = 'professional_operations';
    case KID_OPERATIONS = 'kid_operations';
    case CHECKLIST_OPERATIONS = 'checklist_operations';
    case COMPETENCE_OPERATIONS = 'competence_operations';
    case AUTHENTICATION = 'authentication';
    case AUTHORIZATION = 'authorization';
    case SYSTEM_OPERATIONS = 'system_operations';
    case SECURITY_EVENTS = 'security_events';
    case PERFORMANCE_MONITORING = 'performance_monitoring';
    case DATA_VALIDATION = 'data_validation';
    case FILE_OPERATIONS = 'file_operations';
    case EMAIL_OPERATIONS = 'email_operations';
    case ERROR_HANDLING = 'error_handling';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::USER_OPERATIONS => 'Operações de Usuário',
            self::PROFESSIONAL_OPERATIONS => 'Operações de Profissional',
            self::KID_OPERATIONS => 'Operações de Criança',
            self::CHECKLIST_OPERATIONS => 'Operações de Checklist',
            self::COMPETENCE_OPERATIONS => 'Operações de Competência',
            self::AUTHENTICATION => 'Autenticação',
            self::AUTHORIZATION => 'Autorização',
            self::SYSTEM_OPERATIONS => 'Operações do Sistema',
            self::SECURITY_EVENTS => 'Eventos de Segurança',
            self::PERFORMANCE_MONITORING => 'Monitoramento de Performance',
            self::DATA_VALIDATION => 'Validação de Dados',
            self::FILE_OPERATIONS => 'Operações de Arquivo',
            self::EMAIL_OPERATIONS => 'Operações de Email',
            self::ERROR_HANDLING => 'Tratamento de Erros',
        };
    }

    public function getChannel(): string
    {
        return match ($this) {
            self::SECURITY_EVENTS, self::AUTHENTICATION, self::AUTHORIZATION => 'security',
            self::PERFORMANCE_MONITORING => 'performance',
            self::ERROR_HANDLING => 'errors',
            default => 'application'
        };
    }
}
