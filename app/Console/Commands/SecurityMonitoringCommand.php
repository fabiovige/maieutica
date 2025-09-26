<?php

namespace App\Console\Commands;

use App\Services\Security\SecurityMonitoringService;
use Illuminate\Console\Command;

class SecurityMonitoringCommand extends Command
{
    protected $signature = 'security:monitor {--metrics : Show metrics only}';

    protected $description = 'Execute security monitoring and threat detection';

    public function __construct(
        private readonly SecurityMonitoringService $monitoringService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Iniciando monitoramento de segurança...');

        if ($this->option('metrics')) {
            return $this->showMetrics();
        }

        try {
            $results = $this->monitoringService->checkSecurityThreats();

            $this->info("Verificação concluída em: {$results['timestamp']}");
            $this->info("Status do sistema: {$results['system_status']}");
            $this->info("Alertas encontrados: {$results['alerts_count']}");

            if ($results['alerts_count'] > 0) {
                $this->newLine();
                $this->warn('🚨 ALERTAS DETECTADOS:');

                foreach ($results['alerts'] as $alert) {
                    $severityColor = match($alert['severity']) {
                        'high' => 'error',
                        'medium' => 'warn',
                        'low' => 'info',
                        default => 'comment'
                    };

                    $this->newLine();
                    $this->{$severityColor}("[{$alert['severity']}] {$alert['title']}");
                    $this->line("  {$alert['message']}");

                    if (!empty($alert['data'])) {
                        $this->line('  Dados: ' . json_encode($alert['data'], JSON_PRETTY_PRINT));
                    }
                }
            } else {
                $this->info('✅ Nenhuma ameaça detectada.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erro durante monitoramento: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function showMetrics()
    {
        $this->info('Coletando métricas do sistema...');

        try {
            $metrics = $this->monitoringService->getMetrics();

            $this->newLine();
            $this->info('📊 MÉTRICAS DIÁRIAS:');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Novos usuários hoje', $metrics['daily_metrics']['total_users']],
                    ['Sessões ativas', $metrics['daily_metrics']['active_sessions']],
                    ['Logins falharam hoje', $metrics['daily_metrics']['failed_logins']],
                    ['Eventos de segurança hoje', $metrics['daily_metrics']['security_events']],
                ]
            );

            $this->newLine();
            $this->info('📈 MÉTRICAS SEMANAIS:');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Novos usuários (7 dias)', $metrics['weekly_metrics']['new_users']],
                    ['Total de logins (7 dias)', $metrics['weekly_metrics']['total_logins']],
                    ['Logins falharam (7 dias)', $metrics['weekly_metrics']['failed_logins']],
                    ['Alertas de segurança (7 dias)', $metrics['weekly_metrics']['security_alerts']],
                ]
            );

            $this->newLine();
            $this->info('🔧 SAÚDE DO SISTEMA:');
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Uso do disco', $metrics['system_health']['disk_usage'] . '%'],
                    ['Taxa de erros', $metrics['system_health']['error_rate'] . '%'],
                    ['Tempo de resposta médio', $metrics['system_health']['average_response_time'] . 's'],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erro ao coletar métricas: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}