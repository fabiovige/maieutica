<?php

namespace App\Console\Commands;

use App\Services\Security\SecurityTestService;
use Illuminate\Console\Command;

class SecurityTestCommand extends Command
{
    protected $signature = 'security:test {--category= : Test specific category} {--json : Output results as JSON} {--report : Generate detailed report}';

    protected $description = 'Run automated security tests';

    public function __construct(
        private readonly SecurityTestService $securityTestService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔒 Iniciando testes de segurança automatizados...');
        $this->newLine();

        try {
            $results = $this->securityTestService->runAllTests();

            if ($this->option('json')) {
                $this->line(json_encode($results, JSON_PRETTY_PRINT));
                return Command::SUCCESS;
            }

            if ($this->option('report')) {
                return $this->generateDetailedReport($results);
            }

            return $this->displaySummary($results);

        } catch (\Exception $e) {
            $this->error("❌ Erro durante os testes de segurança: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function displaySummary(array $results): int
    {
        // Cabeçalho com resumo
        $statusIcon = $results['overall_status'] === 'success' ? '✅' : '⚠️';
        $this->line("$statusIcon <options=bold>Resumo dos Testes de Segurança</>");
        $this->newLine();

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Status Geral', $statusIcon . ' ' . ucfirst($results['overall_status'])],
                ['Taxa de Sucesso', $results['success_rate'] . '%'],
                ['Testes Aprovados', $results['tests_passed'] . '/' . $results['total_tests']],
                ['Testes Reprovados', $results['tests_failed']],
                ['Timestamp', $results['timestamp']]
            ]
        );

        $this->newLine();

        // Resumo por categoria
        $this->line('📊 <options=bold>Resultados por Categoria:</>');
        $this->newLine();

        $categoryData = [];
        foreach ($results['categories'] as $name => $category) {
            $status = $category['failed'] === 0 ? '✅ Passou' : '⚠️  Falhou';
            $categoryData[] = [
                $category['category'],
                $status,
                $category['passed'] . '/' . $category['total'],
                $category['failed'] > 0 ? $category['failed'] . ' falhas' : 'Nenhuma'
            ];
        }

        $this->table(
            ['Categoria', 'Status', 'Aprovados/Total', 'Problemas'],
            $categoryData
        );

        // Mostrar falhas se houver
        if ($results['tests_failed'] > 0) {
            $this->newLine();
            $this->warn('⚠️  Testes que falharam:');
            $this->newLine();

            foreach ($results['categories'] as $category) {
                foreach ($category['tests'] as $test) {
                    if (!$test['passed']) {
                        $this->error("❌ {$test['name']}: {$test['message']}");
                    }
                }
            }
        }

        $this->newLine();

        if ($results['overall_status'] === 'success') {
            $this->info('🎉 Todos os testes de segurança passaram!');
        } else {
            $this->warn('⚠️  Alguns testes falharam. Revise os problemas identificados.');
        }

        return $results['overall_status'] === 'success' ? Command::SUCCESS : Command::FAILURE;
    }

    private function generateDetailedReport(array $results): int
    {
        $this->info('📋 Relatório Detalhado de Segurança');
        $this->newLine();

        // Cabeçalho do relatório
        $this->line('=' . str_repeat('=', 70));
        $this->line('  RELATÓRIO DE SEGURANÇA - SISTEMA MAIÊUTICA');
        $this->line('  Gerado em: ' . $results['timestamp']);
        $this->line('=' . str_repeat('=', 70));
        $this->newLine();

        // Resumo executivo
        $this->info('🎯 RESUMO EXECUTIVO:');
        $this->line("Status Geral: {$results['overall_status']}");
        $this->line("Taxa de Sucesso: {$results['success_rate']}%");
        $this->line("Testes Executados: {$results['total_tests']}");
        $this->line("Testes Aprovados: {$results['tests_passed']}");
        $this->line("Testes Reprovados: {$results['tests_failed']}");
        $this->newLine();

        // Detalhes por categoria
        foreach ($results['categories'] as $name => $category) {
            $this->line('-' . str_repeat('-', 50));
            $categoryIcon = $category['failed'] === 0 ? '✅' : '⚠️';
            $this->line("$categoryIcon <options=bold>{$category['category']}</>");
            $this->line("Aprovados: {$category['passed']}/{$category['total']}");
            $this->newLine();

            // Detalhes dos testes
            foreach ($category['tests'] as $test) {
                $testIcon = $test['passed'] ? '✅' : '❌';
                $this->line("  $testIcon {$test['name']}");
                $this->line("     " . $test['message']);

                if (!empty($test['details'])) {
                    $this->line('     Detalhes:');
                    foreach ($test['details'] as $key => $value) {
                        $displayValue = is_bool($value) ? ($value ? 'Sim' : 'Não') : $value;
                        $this->line("       - " . ucfirst(str_replace('_', ' ', $key)) . ": $displayValue");
                    }
                }
                $this->newLine();
            }
        }

        // Recomendações
        $this->line('=' . str_repeat('=', 70));
        $this->info('💡 RECOMENDAÇÕES:');
        $this->newLine();

        if ($results['tests_failed'] === 0) {
            $this->line('• Sistema está em conformidade com os padrões de segurança testados');
            $this->line('• Continue monitorando regularmente com estes testes');
            $this->line('• Considere executar estes testes antes de cada deploy em produção');
        } else {
            $this->line('• Revisar e corrigir os testes que falharam antes do deploy em produção');
            $this->line('• Implementar monitoramento contínuo de segurança');
            $this->line('• Considerar auditoria de segurança mais detalhada se múltiplos testes falharam');
        }

        $this->newLine();
        $this->line('=' . str_repeat('=', 70));

        return $results['overall_status'] === 'success' ? Command::SUCCESS : Command::FAILURE;
    }
}