<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Responsible;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DecryptUserNamesCommand extends Command
{
    protected $signature = 'decrypt:user-names {--dry-run : Show what would be changed without making changes}';

    protected $description = 'Decrypt encrypted user and responsible names in the database';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->info('⚠️  Starting decryption of user and responsible names...');
        }

        $this->newLine();

        // Decrypt User names
        $this->info('🔄 Processing Users...');
        $userCount = $this->decryptUserNames($dryRun);

        // Decrypt Responsible names
        $this->info('🔄 Processing Responsibles...');
        $responsibleCount = $this->decryptResponsibleNames($dryRun);

        $this->newLine();

        if ($dryRun) {
            $this->info("✅ DRY RUN completed:");
            $this->info("   - Users that would be updated: {$userCount}");
            $this->info("   - Responsibles that would be updated: {$responsibleCount}");
            $this->info("   - Run without --dry-run to apply changes");
        } else {
            $this->info("✅ Decryption completed:");
            $this->info("   - Users updated: {$userCount}");
            $this->info("   - Responsibles updated: {$responsibleCount}");
        }

        return Command::SUCCESS;
    }

    private function decryptUserNames(bool $dryRun): int
    {
        $count = 0;

        User::chunk(50, function ($users) use (&$count, $dryRun) {
            foreach ($users as $user) {
                $rawName = DB::table('users')->where('id', $user->id)->value('name');

                if (empty($rawName)) {
                    continue;
                }

                try {
                    $decrypted = $this->attemptDecrypt($rawName);

                    if ($decrypted && $decrypted !== $rawName) {
                        $this->line("   User ID {$user->id}: '{$decrypted}'");

                        if (!$dryRun) {
                            DB::table('users')->where('id', $user->id)->update(['name' => $decrypted]);
                        }

                        $count++;
                    }
                } catch (\Exception $e) {
                    $this->warn("   User ID {$user->id}: Failed to decrypt - {$e->getMessage()}");
                }
            }
        });

        return $count;
    }

    private function decryptResponsibleNames(bool $dryRun): int
    {
        $count = 0;

        Responsible::chunk(50, function ($responsibles) use (&$count, $dryRun) {
            foreach ($responsibles as $responsible) {
                $rawName = DB::table('responsibles')->where('id', $responsible->id)->value('name');

                if (empty($rawName)) {
                    continue;
                }

                try {
                    $decrypted = $this->attemptDecrypt($rawName);

                    if ($decrypted && $decrypted !== $rawName) {
                        $this->line("   Responsible ID {$responsible->id}: '{$decrypted}'");

                        if (!$dryRun) {
                            DB::table('responsibles')->where('id', $responsible->id)->update(['name' => $decrypted]);
                        }

                        $count++;
                    }
                } catch (\Exception $e) {
                    $this->warn("   Responsible ID {$responsible->id}: Failed to decrypt - {$e->getMessage()}");
                }
            }
        });

        return $count;
    }

    private function attemptDecrypt(string $encryptedValue): ?string
    {
        try {
            // First decryption attempt
            $firstDecrypt = Crypt::decryptString($encryptedValue);

            // Check if it's double-encrypted (JSON format indicates encryption)
            if (str_starts_with($firstDecrypt, 'eyJ') || (json_decode($firstDecrypt) && str_contains($firstDecrypt, '"iv"'))) {
                try {
                    // Second decryption attempt for double-encrypted values
                    return Crypt::decryptString($firstDecrypt);
                } catch (\Exception $e) {
                    // If second decrypt fails, return first decrypt result
                    return $firstDecrypt;
                }
            }

            return $firstDecrypt;

        } catch (\Exception $e) {
            // If not encrypted, return original value
            return $encryptedValue;
        }
    }
}
