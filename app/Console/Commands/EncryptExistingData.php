<?php

namespace App\Console\Commands;

use App\Models\Kid;
use App\Models\Responsible;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EncryptExistingData extends Command
{
    protected $signature = 'encrypt:existing-data
                            {--model= : Specific model to encrypt (Kid, User, Responsible)}
                            {--force : Skip confirmation prompts}
                            {--backup : Create backup before encryption}';

    protected $description = 'Encrypt existing sensitive data in compliance with LGPD';

    protected $models = [
        'Kid' => Kid::class,
        'User' => User::class,
        'Responsible' => Responsible::class,
    ];

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will encrypt existing sensitive data. Do you want to continue?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('Starting encryption process...');

        if ($this->option('backup')) {
            $this->createBackup();
        }

        $specificModel = $this->option('model');

        if ($specificModel) {
            $this->encryptModelData($specificModel);
        } else {
            foreach ($this->models as $modelName => $modelClass) {
                $this->encryptModelData($modelName);
            }
        }

        $this->info('Encryption process completed successfully!');

        return Command::SUCCESS;
    }

    protected function createBackup()
    {
        $this->info('Creating database backup...');

        $timestamp = now()->format('Y_m_d_H_i_s');
        $backupFile = "backup_before_encryption_{$timestamp}.sql";

        $this->line("Backup file will be: {$backupFile}");
        $this->info('Please ensure you have a proper backup before proceeding.');

        if (!$this->option('force')) {
            if (!$this->confirm('Have you created a backup? Continue?')) {
                $this->error('Please create a backup before proceeding.');
                exit;
            }
        }
    }

    protected function encryptModelData($modelName)
    {
        if (!isset($this->models[$modelName])) {
            $this->error("Model {$modelName} not found.");
            return;
        }

        $modelClass = $this->models[$modelName];
        $this->info("Encrypting data for model: {$modelName}");

        $model = new $modelClass();
        $encryptedFields = $model->getEncryptedFields();

        if (empty($encryptedFields)) {
            $this->warn("No encrypted fields defined for {$modelName}");
            return;
        }

        $this->line("Encrypted fields: " . implode(', ', $encryptedFields));

        $records = $modelClass::withTrashed()->get();
        $totalRecords = $records->count();

        if ($totalRecords === 0) {
            $this->warn("No records found for {$modelName}");
            return;
        }

        $this->info("Processing {$totalRecords} records...");
        $progressBar = $this->output->createProgressBar($totalRecords);

        $encrypted = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($records as $record) {
            try {
                $needsUpdate = false;
                $updates = [];

                foreach ($encryptedFields as $field) {
                    $rawValue = $record->getRawOriginal($field);

                    if ($rawValue && !$record->isFieldEncrypted($field)) {
                        $updates[$field] = $rawValue;
                        $needsUpdate = true;
                    }
                }

                if ($needsUpdate) {
                    DB::table($record->getTable())
                        ->where($record->getKeyName(), $record->getKey())
                        ->update($updates);
                    $encrypted++;
                } else {
                    $skipped++;
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("\nError processing record ID {$record->getKey()}: " . $e->getMessage());
                $errors++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Model {$modelName} results:");
        $this->line("  Encrypted: {$encrypted}");
        $this->line("  Skipped (already encrypted): {$skipped}");
        $this->line("  Errors: {$errors}");
        $this->newLine();
    }

    protected function verifyEncryption()
    {
        $this->info('Verifying encryption integrity...');

        foreach ($this->models as $modelName => $modelClass) {
            $this->line("Checking {$modelName}...");

            $records = $modelClass::take(5)->get();

            foreach ($records as $record) {
                $model = new $modelClass();
                $encryptedFields = $model->getEncryptedFields();

                foreach ($encryptedFields as $field) {
                    $decrypted = $record->{$field};
                    $encrypted = $record->getEncryptedRawAttribute($field);

                    if ($encrypted && $decrypted) {
                        $this->line("  ✓ {$field} encryption verified");
                    }
                }
            }
        }
    }
}
