<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MigrateKidsPhotos extends Command
{
    protected $signature = 'kids:migrate-photos {--dry-run : Only show what would be done without making changes} {--force : Execute migration without confirmation}';

    protected $description = 'Migrate kids photos from public directory to secure private storage';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Iniciando migração de fotos de crianças para storage seguro...');

        if ($dryRun) {
            $this->warn('MODO DRY RUN - Nenhuma alteração será feita');
        }

        $publicPhotosPath = public_path('images/kids');

        if (!is_dir($publicPhotosPath)) {
            $this->error("Diretório {$publicPhotosPath} não existe");
            return Command::FAILURE;
        }

        $kids = Kid::whereNotNull('photo')->get();
        $this->info("Encontradas {$kids->count()} crianças com fotos");

        if ($kids->isEmpty()) {
            $this->info('Nenhuma criança com foto encontrada');
            return Command::SUCCESS;
        }

        if (!$dryRun && !$force) {
            if (!$this->confirm('Deseja continuar com a migração?')) {
                $this->info('Migração cancelada');
                return Command::SUCCESS;
            }
        }

        $migrated = 0;
        $errors = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($kids->count());
        $progressBar->start();

        foreach ($kids as $kid) {
            try {
                $result = $this->migrateKidPhoto($kid, $dryRun);

                switch ($result) {
                    case 'migrated':
                        $migrated++;
                        break;
                    case 'skipped':
                        $skipped++;
                        break;
                    case 'error':
                        $errors++;
                        break;
                }
            } catch (\Exception $e) {
                $this->error("\nErro ao migrar foto da criança {$kid->id}: {$e->getMessage()}");
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(['Status', 'Quantidade'], [
            ['Migradas', $migrated],
            ['Ignoradas', $skipped],
            ['Erros', $errors],
            ['Total', $kids->count()],
        ]);

        if ($dryRun) {
            $this->info('Simulação concluída. Use --force para executar a migração real.');
        } else {
            $this->info('Migração concluída!');
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function migrateKidPhoto(Kid $kid, bool $dryRun): string
    {
        $oldPhotoPath = public_path("images/kids/{$kid->photo}");

        if (!file_exists($oldPhotoPath)) {
            $this->warn("\nFoto não encontrada: {$oldPhotoPath}");
            return 'skipped';
        }

        $kidDirectory = (string) $kid->id;
        $privatePhotoPath = "{$kidDirectory}/{$kid->photo}";

        if (Storage::disk('kids_photos')->exists($privatePhotoPath)) {
            $this->warn("\nFoto já existe no storage privado: {$privatePhotoPath}");
            return 'skipped';
        }

        if ($dryRun) {
            $this->line("\n[DRY RUN] Migraria: {$oldPhotoPath} -> storage/app/private/kids/{$privatePhotoPath}");
            return 'migrated';
        }

        if (!Storage::disk('kids_photos')->exists($kidDirectory)) {
            Storage::disk('kids_photos')->makeDirectory($kidDirectory);
        }

        $fileContents = file_get_contents($oldPhotoPath);
        if ($fileContents === false) {
            $this->error("\nErro ao ler arquivo: {$oldPhotoPath}");
            return 'error';
        }

        $success = Storage::disk('kids_photos')->put($privatePhotoPath, $fileContents);
        if (!$success) {
            $this->error("\nErro ao salvar arquivo no storage privado: {$privatePhotoPath}");
            return 'error';
        }

        if (Storage::disk('kids_photos')->exists($privatePhotoPath)) {
            unlink($oldPhotoPath);
            $this->line("\n✓ Migrada: {$kid->name} (ID: {$kid->id})");
            return 'migrated';
        }

        $this->error("\nFalha na verificação pós-migração para criança {$kid->id}");
        return 'error';
    }
}
