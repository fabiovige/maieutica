<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class CleanOldAuditLogs extends Command
{
    protected $signature = 'audit:clean
                           {--days=365 : Número de dias para manter os logs}
                           {--dry-run : Executar sem deletar para ver quantos logs seriam removidos}';

    protected $description = 'Limpa logs de auditoria antigos mantendo apenas os mais recentes';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Limpeza de logs de auditoria LGPD");
        $this->info("Mantendo logs dos últimos {$days} dias");

        if ($dryRun) {
            $this->warn("MODO DRY-RUN - Nenhum log será deletado");
        }

        $cutoffDate = now()->subDays($days);
        $this->info("Data de corte: {$cutoffDate->format('d/m/Y H:i:s')}");

        $logsToDelete = AuditLog::where('created_at', '<', $cutoffDate);
        $count = $logsToDelete->count();

        if ($count === 0) {
            $this->info("Nenhum log encontrado para limpeza.");
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$count} logs para limpeza");

        if ($dryRun) {
            $this->table(['Estatísticas'], [
                ['Total de logs para deletar', $count],
                ['Ações mais comuns para deletar', $this->getMostCommonActions($logsToDelete)],
                ['Recursos mais comuns para deletar', $this->getMostCommonResources($logsToDelete)],
            ]);

            return Command::SUCCESS;
        }

        if ($this->confirm("Deseja continuar com a limpeza de {$count} logs?")) {
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            $logsToDelete->chunkById(1000, function ($logs) use ($bar) {
                foreach ($logs as $log) {
                    $log->delete();
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();
            $this->info("Limpeza concluída! {$count} logs foram removidos.");

            $remaining = AuditLog::count();
            $this->info("Logs restantes no sistema: {$remaining}");
        } else {
            $this->info("Limpeza cancelada.");
        }

        return Command::SUCCESS;
    }

    private function getMostCommonActions($query): string
    {
        $actions = $query->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->pluck('count', 'action')
            ->toArray();

        return collect($actions)->map(fn ($count, $action) => "{$action}: {$count}")->implode(', ');
    }

    private function getMostCommonResources($query): string
    {
        $resources = $query->selectRaw('resource, COUNT(*) as count')
            ->groupBy('resource')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->pluck('count', 'resource')
            ->toArray();

        return collect($resources)->map(fn ($count, $resource) => "{$resource}: {$count}")->implode(', ');
    }
}
