<?php

namespace App\Services\Backup;

use App\Services\Log\LoggingService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;

class BackupService
{
    private const BACKUP_DISK = 'backups';

    public function __construct(
        private readonly LoggingService $loggingService
    ) {}

    public function performFullBackup(): array
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $backupName = "maieutica_backup_{$timestamp}";

        try {
            $results = [
                'backup_name' => $backupName,
                'timestamp' => $timestamp,
                'status' => 'success',
                'components' => []
            ];

            $results['components']['database'] = $this->backupDatabase($backupName);
            $results['components']['files'] = $this->backupFiles($backupName);
            $results['components']['archive'] = $this->createBackupArchive($backupName);
            $results['components']['cleanup'] = $this->cleanupOldBackups();

            $results['total_size'] = $this->getBackupSize($backupName);

            $this->loggingService->logUserOperation(
                'BACKUP_COMPLETED',
                'Backup completo realizado com sucesso',
                [
                    'backup_name' => $backupName,
                    'total_size' => $results['total_size'],
                    'components' => array_keys($results['components'])
                ]
            );

            return $results;

        } catch (\Exception $e) {
            $this->loggingService->logUserOperation(
                'BACKUP_FAILED',
                'Falha no backup completo',
                [
                    'backup_name' => $backupName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            );

            throw $e;
        }
    }

    private function backupDatabase(string $backupName): array
    {
        $databaseName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $backupPath = storage_path("app/backups/temp/{$backupName}/database.sql");
        $this->ensureDirectoryExists(dirname($backupPath));

        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($databaseName),
            escapeshellarg($backupPath)
        );

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Falha no backup do banco de dados: " . implode("\n", $output));
        }

        $fileSize = filesize($backupPath);

        return [
            'status' => 'success',
            'file_path' => $backupPath,
            'file_size' => $fileSize,
            'tables_backed_up' => $this->getTableCount()
        ];
    }

    private function backupFiles(string $backupName): array
    {
        $backupDir = storage_path("app/backups/temp/{$backupName}/files");
        $this->ensureDirectoryExists($backupDir);

        $filesToBackup = [
            'kids_photos' => storage_path('app/private/kids'),
            'user_avatars' => storage_path('app/public/images/avatars'),
            'app_config' => base_path('.env'),
            'logs' => storage_path('logs')
        ];

        $results = [];
        $totalSize = 0;
        $totalFiles = 0;

        foreach ($filesToBackup as $category => $sourcePath) {
            if (!file_exists($sourcePath)) {
                $results[$category] = [
                    'status' => 'skipped',
                    'reason' => 'Path does not exist'
                ];
                continue;
            }

            $destPath = "{$backupDir}/{$category}";

            if (is_file($sourcePath)) {
                $this->ensureDirectoryExists(dirname($destPath));
                copy($sourcePath, $destPath);
                $size = filesize($destPath);
                $files = 1;
            } else {
                $result = $this->copyDirectory($sourcePath, $destPath);
                $size = $result['total_size'];
                $files = $result['file_count'];
            }

            $totalSize += $size;
            $totalFiles += $files;

            $results[$category] = [
                'status' => 'success',
                'file_count' => $files,
                'size' => $size
            ];
        }

        return [
            'status' => 'success',
            'categories' => $results,
            'total_files' => $totalFiles,
            'total_size' => $totalSize
        ];
    }

    private function createBackupArchive(string $backupName): array
    {
        $tempDir = storage_path("app/backups/temp/{$backupName}");
        $archivePath = storage_path("app/backups/{$backupName}.zip");

        $this->ensureDirectoryExists(dirname($archivePath));

        $zip = new ZipArchive();

        if ($zip->open($archivePath, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception("Não foi possível criar o arquivo de backup: {$archivePath}");
        }

        $this->addDirectoryToZip($zip, $tempDir, '');
        $zip->close();

        $archiveSize = filesize($archivePath);

        File::deleteDirectory($tempDir);

        return [
            'status' => 'success',
            'archive_path' => $archivePath,
            'archive_size' => $archiveSize,
            'compression_ratio' => 0.7 // Estimativa
        ];
    }

    private function cleanupOldBackups(): array
    {
        $retentionDays = config('backup.retention_days', 30);
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $backupDir = storage_path('app/backups');
        $deletedFiles = [];
        $deletedSize = 0;

        if (!is_dir($backupDir)) {
            return [
                'status' => 'skipped',
                'reason' => 'Backup directory does not exist'
            ];
        }

        $files = glob("{$backupDir}/maieutica_backup_*.zip");

        foreach ($files as $file) {
            $fileTime = Carbon::createFromTimestamp(filemtime($file));

            if ($fileTime->lt($cutoffDate)) {
                $size = filesize($file);
                if (unlink($file)) {
                    $deletedFiles[] = basename($file);
                    $deletedSize += $size;
                }
            }
        }

        return [
            'status' => 'success',
            'deleted_files' => $deletedFiles,
            'deleted_count' => count($deletedFiles),
            'freed_space' => $deletedSize
        ];
    }

    public function listBackups(): array
    {
        $backupDir = storage_path('app/backups');
        $backups = [];

        if (!is_dir($backupDir)) {
            return $backups;
        }

        $files = glob("{$backupDir}/maieutica_backup_*.zip");

        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            $created = Carbon::createFromTimestamp(filemtime($file));

            preg_match('/maieutica_backup_(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})\.zip/', $filename, $matches);
            $timestamp = $matches[1] ?? 'unknown';

            $backups[] = [
                'filename' => $filename,
                'full_path' => $file,
                'size' => $size,
                'size_human' => $this->formatBytes($size),
                'created_at' => $created,
                'timestamp' => $timestamp,
                'age_days' => $created->diffInDays(Carbon::now())
            ];
        }

        usort($backups, fn($a, $b) => $b['created_at']->timestamp - $a['created_at']->timestamp);

        return $backups;
    }

    public function downloadBackup(string $filename): ?string
    {
        $filePath = storage_path("app/backups/{$filename}");

        if (!file_exists($filePath)) {
            return null;
        }

        return $filePath;
    }

    public function deleteBackup(string $filename): bool
    {
        $filePath = storage_path("app/backups/{$filename}");

        if (!file_exists($filePath)) {
            return false;
        }

        $result = unlink($filePath);

        if ($result) {
            $this->loggingService->logUserOperation(
                'BACKUP_DELETED',
                'Backup excluído manualmente',
                ['filename' => $filename]
            );
        }

        return $result;
    }

    public function getBackupInfo(): array
    {
        $backups = $this->listBackups();
        $totalSize = array_sum(array_column($backups, 'size'));
        $availableSpace = disk_free_space(storage_path('app/backups'));

        return [
            'total_backups' => count($backups),
            'total_size' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'available_space' => $availableSpace,
            'available_space_human' => $this->formatBytes($availableSpace),
            'oldest_backup' => $backups ? end($backups)['created_at'] : null,
            'newest_backup' => $backups ? $backups[0]['created_at'] : null,
            'retention_days' => config('backup.retention_days', 30)
        ];
    }

    private function copyDirectory(string $source, string $destination): array
    {
        if (!is_dir($source)) {
            return ['file_count' => 0, 'total_size' => 0];
        }

        $this->ensureDirectoryExists($destination);

        $fileCount = 0;
        $totalSize = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

            if ($item->isDir()) {
                $this->ensureDirectoryExists($destPath);
            } else {
                copy($item, $destPath);
                $fileCount++;
                $totalSize += $item->getSize();
            }
        }

        return ['file_count' => $fileCount, 'total_size' => $totalSize];
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $relativePath = $zipPath . ($zipPath ? DIRECTORY_SEPARATOR : '') . $iterator->getSubPathName();

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($file, $relativePath);
            }
        }
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    private function getTableCount(): int
    {
        return DB::select('SHOW TABLES') ? count(DB::select('SHOW TABLES')) : 0;
    }

    private function getBackupSize(string $backupName): int
    {
        $archivePath = storage_path("app/backups/{$backupName}.zip");
        return file_exists($archivePath) ? filesize($archivePath) : 0;
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