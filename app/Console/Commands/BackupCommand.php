<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Illuminate\Console\Command;

class BackupCommand extends Command
{
    protected $signature = 'backup:run {--list : List existing backups} {--info : Show backup information} {--clean : Clean old backups only}';

    protected $description = 'Create system backups or manage existing backups';

    public function __construct(
        private readonly BackupService $backupService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('list')) {
            return $this->listBackups();
        }

        if ($this->option('info')) {
            return $this->showBackupInfo();
        }

        if ($this->option('clean')) {
            return $this->cleanOldBackups();
        }

        return $this->createBackup();
    }

    private function createBackup()
    {
        $this->info('🔄 Iniciando backup completo do sistema...');
        $this->newLine();

        try {
            $progressBar = $this->output->createProgressBar(4);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

            $progressBar->setMessage('Preparando backup...');
            $progressBar->start();

            $results = $this->backupService->performFullBackup();

            $progressBar->setMessage('Backup do banco de dados...');
            $progressBar->advance();

            $progressBar->setMessage('Backup dos arquivos...');
            $progressBar->advance();

            $progressBar->setMessage('Criando arquivo compactado...');
            $progressBar->advance();

            $progressBar->setMessage('Limpando backups antigos...');
            $progressBar->advance();

            $progressBar->setMessage('Backup concluído!');
            $progressBar->finish();

            $this->newLine(2);
            $this->info('✅ Backup concluído com sucesso!');
            $this->newLine();

            $this->table(
                ['Componente', 'Status', 'Detalhes'],
                [
                    [
                        'Banco de Dados',
                        '✅ ' . $results['components']['database']['status'],
                        $this->formatBytes($results['components']['database']['file_size']) .
                        ' (' . $results['components']['database']['tables_backed_up'] . ' tabelas)'
                    ],
                    [
                        'Arquivos',
                        '✅ ' . $results['components']['files']['status'],
                        $results['components']['files']['total_files'] . ' arquivos (' .
                        $this->formatBytes($results['components']['files']['total_size']) . ')'
                    ],
                    [
                        'Arquivo Final',
                        '✅ ' . $results['components']['archive']['status'],
                        $this->formatBytes($results['components']['archive']['archive_size'])
                    ],
                    [
                        'Limpeza',
                        '✅ ' . $results['components']['cleanup']['status'],
                        $results['components']['cleanup']['deleted_count'] . ' backups antigos removidos'
                    ]
                ]
            );

            $this->newLine();
            $this->info("📦 Backup salvo: {$results['backup_name']}.zip");
            $this->info("📊 Tamanho total: " . $this->formatBytes($results['total_size']));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error("❌ Erro durante o backup: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function listBackups()
    {
        $this->info('📋 Lista de Backups Existentes:');
        $this->newLine();

        try {
            $backups = $this->backupService->listBackups();

            if (empty($backups)) {
                $this->warn('Nenhum backup encontrado.');
                return Command::SUCCESS;
            }

            $tableData = [];
            foreach ($backups as $backup) {
                $tableData[] = [
                    $backup['filename'],
                    $backup['size_human'],
                    $backup['created_at']->format('d/m/Y H:i:s'),
                    $backup['age_days'] . ' dias'
                ];
            }

            $this->table(
                ['Nome do Arquivo', 'Tamanho', 'Criado em', 'Idade'],
                $tableData
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erro ao listar backups: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function showBackupInfo()
    {
        $this->info('ℹ️  Informações do Sistema de Backup:');
        $this->newLine();

        try {
            $info = $this->backupService->getBackupInfo();

            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Total de Backups', $info['total_backups']],
                    ['Espaço Usado', $info['total_size_human']],
                    ['Espaço Disponível', $info['available_space_human']],
                    ['Backup Mais Antigo', $info['oldest_backup'] ? $info['oldest_backup']->format('d/m/Y H:i:s') : 'N/A'],
                    ['Backup Mais Recente', $info['newest_backup'] ? $info['newest_backup']->format('d/m/Y H:i:s') : 'N/A'],
                    ['Retenção (dias)', $info['retention_days']]
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erro ao obter informações: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function cleanOldBackups()
    {
        $this->info('🧹 Limpando backups antigos...');

        try {
            $results = $this->backupService->performFullBackup();
            $cleanup = $results['components']['cleanup'];

            if ($cleanup['deleted_count'] > 0) {
                $this->info("✅ Removidos {$cleanup['deleted_count']} backups antigos");
                $this->info("💾 Espaço liberado: " . $this->formatBytes($cleanup['freed_space']));

                if (!empty($cleanup['deleted_files'])) {
                    $this->newLine();
                    $this->info('Arquivos removidos:');
                    foreach ($cleanup['deleted_files'] as $file) {
                        $this->line("  - {$file}");
                    }
                }
            } else {
                $this->info('ℹ️  Nenhum backup antigo encontrado para remoção');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erro durante limpeza: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}