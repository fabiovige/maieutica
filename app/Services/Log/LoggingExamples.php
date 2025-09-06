<?php

namespace App\Services\Log;

use App\Enums\LogCategory;
use App\Enums\LogOperation;
use App\Models\User;
use App\Models\Kid;

/**
 * Exemplos de uso do Sistema Centralizado de Logging do Maiêutica
 * 
 * Esta classe contém exemplos práticos de como utilizar o LoggingService
 * em diferentes cenários da aplicação.
 */
class LoggingExamples
{
    /**
     * EXEMPLO 1: Logging de Operações de Usuário
     */
    public function userOperationExamples(): void
    {
        $user = User::find(1);

        // Login bem-sucedido
        LoggingService::logAuthentication(
            LogOperation::LOGIN,
            "Login realizado com sucesso",
            ['user_email' => $user->email]
        );

        // Tentativa de login falhada
        LoggingService::logAuthentication(
            LogOperation::LOGIN,
            "Tentativa de login falhada",
            ['attempted_email' => 'usuario@exemplo.com', 'reason' => 'Credenciais inválidas'],
            'warning'
        );

        // Criação de usuário
        LoggingService::logUserOperation(
            LogOperation::CREATE,
            "Novo usuário criado",
            ['user_id' => $user->id, 'user_role' => $user->roles->first()->name]
        );
    }

    /**
     * EXEMPLO 2: Logging de Operações de Criança
     */
    public function kidOperationExamples(): void
    {
        $kid = Kid::find(1);

        // Visualização de perfil
        LoggingService::logKidOperation(
            LogOperation::READ,
            "Perfil de criança visualizado",
            ['kid_id' => $kid->id, 'kid_name' => $kid->name]
        );

        // Atualização de dados
        LoggingService::logKidOperation(
            LogOperation::UPDATE,
            "Dados da criança atualizados",
            [
                'kid_id' => $kid->id,
                'updated_fields' => ['name', 'birth_date'],
                'previous_values' => ['name' => 'Nome Anterior']
            ]
        );
    }

    /**
     * EXEMPLO 3: Logging de Eventos de Segurança
     */
    public function securityEventExamples(): void
    {
        // Acesso negado
        LoggingService::logSecurityEvent(
            LogOperation::ACCESS_DENIED,
            "Tentativa de acesso não autorizado",
            [
                'requested_resource' => 'admin_dashboard',
                'user_permissions' => ['view_kids', 'create_checklists']
            ]
        );

        // Múltiplas tentativas de login
        LoggingService::logSecurityEvent(
            LogOperation::LOGIN,
            "Múltiplas tentativas de login detectadas",
            [
                'ip_address' => '192.168.1.100',
                'attempts_count' => 5,
                'time_window' => '5 minutes'
            ]
        );
    }

    /**
     * EXEMPLO 4: Logging de Performance
     */
    public function performanceExamples(): void
    {
        // Monitoramento de query lenta
        LoggingService::logPerformance(
            "Query de checklist executada",
            [
                'query_type' => 'SELECT',
                'table' => 'checklists',
                'conditions' => ['kid_id', 'status']
            ],
            2.5 // tempo de execução em segundos
        );

        // Operação com medição automática
        $result = LoggingService::measureExecution(function () {
            // Simulação de operação pesada
            sleep(1);
            return "Relatório gerado";
        }, "Geração de relatório PDF");
    }

    /**
     * EXEMPLO 5: Logging com Trace ID para operações relacionadas
     */
    public function traceExamples(): void
    {
        $traceId = LoggingService::startTrace("checklist_completion");

        try {
            // Operação 1
            LoggingService::logChecklistOperation(
                LogOperation::UPDATE,
                "Iniciando completar checklist",
                ['checklist_id' => 123]
            );

            // Operação 2 relacionada
            LoggingService::logCompetenceOperation(
                LogOperation::UPDATE,
                "Atualizando competências",
                ['competence_ids' => [1, 2, 3]]
            );

            // Operação 3 relacionada
            LoggingService::logSystemOperation(
                LogOperation::CREATE,
                "Gerando plano de desenvolvimento",
                ['plan_id' => 456]
            );

        } finally {
            LoggingService::endTrace();
        }
    }

    /**
     * EXEMPLO 6: Logging de Exceções
     */
    public function exceptionExamples(): void
    {
        try {
            throw new \Exception("Erro ao processar dados da criança");
        } catch (\Exception $e) {
            LoggingService::logException(
                $e,
                "Falha no processamento de dados",
                ['operation' => 'data_processing', 'kid_id' => 123]
            );
        }
    }

    /**
     * EXEMPLO 7: Logging de Validação de Dados
     */
    public function validationExamples(): void
    {
        // Validação falhada
        LoggingService::logDataValidation(
            LogOperation::VALIDATION_FAILED,
            "Validação de formulário falhada",
            [
                'form_type' => 'kid_registration',
                'failed_fields' => ['birth_date', 'responsible_cpf'],
                'validation_errors' => [
                    'birth_date' => 'Data inválida',
                    'responsible_cpf' => 'CPF já cadastrado'
                ]
            ]
        );
    }

    /**
     * EXEMPLO 8: Logging de Operações de Arquivo
     */
    public function fileOperationExamples(): void
    {
        // Upload de foto
        LoggingService::logFileOperation(
            LogOperation::UPLOAD,
            "Foto da criança enviada",
            [
                'kid_id' => 123,
                'file_name' => 'kid_photo.jpg',
                'file_size' => 2048000,
                'file_type' => 'image/jpeg'
            ]
        );

        // Export de relatório
        LoggingService::logFileOperation(
            LogOperation::EXPORT,
            "Relatório de avaliação exportado",
            [
                'kid_id' => 123,
                'format' => 'PDF',
                'report_type' => 'assessment_report'
            ]
        );
    }

    /**
     * EXEMPLO 9: Uso da Trait HasLogging nos Models
     */
    public function modelTraitExamples(): void
    {
        $kid = Kid::find(1);

        // Logging automático ao visualizar
        $kid->logView('kid_profile');

        // Logging personalizado
        $kid->logCustomOperation(
            LogOperation::UPDATE,
            "Status de avaliação alterado",
            ['previous_status' => 'em_andamento', 'new_status' => 'concluido']
        );

        // Logging de exportação
        $kid->logExport('PDF', ['report_type' => 'comprehensive_assessment']);
    }

    /**
     * EXEMPLO 10: Sanitização Automática de Dados Sensíveis
     */
    public function dataSanitizationExamples(): void
    {
        // Os dados sensíveis serão automaticamente sanitizados
        LoggingService::logUserOperation(
            LogOperation::UPDATE,
            "Perfil de usuário atualizado",
            [
                'user_data' => [
                    'name' => 'João Silva',
                    'email' => 'joao@exemplo.com',
                    'password' => 'senha123', // será automaticamente [REDACTED]
                    'cpf' => '12345678901',   // será mascarado
                    'phone' => '11999999999'  // será mascarado
                ]
            ]
        );
    }
}