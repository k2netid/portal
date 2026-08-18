<?php

namespace Modules\Core\Infra\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Infra\Services\BackupService;

class CreateBackup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:backup {--type=database : Type of backup (database, files, full)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a backup of the system';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        /** @var string $type */
        $type = (string) ($this->option('type') ?? 'database');

        $this->info("Creating {$type} backup...");

        try {
            if ($type === 'full') {
                $backup = $backupService->createFullBackup();
            } elseif ($type === 'files') {
                $backup = $backupService->createFilesBackup();
            } else {
                $backup = $backupService->createDatabaseBackup();
            }

            if ($backup->status === 'completed') {
                $this->info("Backup created successfully: {$backup->name}");
                $this->info('Size: '.round($backup->size / 1024 / 1024, 2).' MB');
                $this->info("Path: {$backup->path}");

                return 0;
            }

            $err = $backup->error_message ?? 'Unknown error';
            $this->error("Backup failed: {$err}");

            return 1;
        } catch (\Throwable $e) {
            $this->error('Backup execution failed: '.$e->getMessage());

            return 1;
        }
    }
}
