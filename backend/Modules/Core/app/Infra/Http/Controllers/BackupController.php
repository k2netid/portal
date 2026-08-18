<?php

namespace Modules\Core\Infra\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Infra\Models\Backup;
use Modules\Core\Infra\Services\BackupService;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends BaseApiController
{
    public function __construct(protected BackupService $backupService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $typeRaw = $request->input('type');
        $type = is_string($typeRaw) ? $typeRaw : null;
        $backups = $this->backupService->listBackups($type);

        return $this->success($backups, 'Backups retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:database,files,full',
        ]);

        try {
            $nameRaw = $request->input('name');
            $name = is_string($nameRaw) ? $nameRaw : null;
            $backup = $this->backupService->createDatabaseBackup($name);

            if ($backup->status === 'failed') {
                return $this->error(
                    $backup->error_message ?? 'Backup creation failed',
                    500,
                    [],
                    'BACKUP_FAILED',
                    ['backup' => $backup]
                );
            }

            return $this->success($backup, 'Backup created successfully', 201);
        } catch (\Throwable $e) {
            try {
                \Log::channel('backup')->error('Backup creation error: '.$e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (\Throwable) {
                \Log::error('Backup creation error (backup channel unavailable): '.$e->getMessage());
            }

            return $this->error(
                'Failed to create backup: '.$e->getMessage(),
                500,
                [],
                'BACKUP_ERROR'
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Backup $backup): JsonResponse
    {
        return $this->success($backup, 'Backup retrieved successfully');
    }

    /**
     * Restore the specified backup.
     */
    public function restore(Request $request, Backup $backup): JsonResponse
    {
        if (! $backup->isCompleted()) {
            return $this->validationError(['backup' => ['Cannot restore incomplete backup']], 'Cannot restore incomplete backup');
        }

        try {
            $this->backupService->restoreDatabaseBackup($backup);

            return $this->success(null, 'Backup restored successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to restore backup: '.$e->getMessage(), 500, [], 'RESTORE_ERROR');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Backup $backup): JsonResponse
    {
        $deletePhysical = $request->boolean('delete_physical', true);
        $this->backupService->deleteBackup($backup, $deletePhysical);

        return $this->success(null, 'Backup deleted successfully');
    }

    /**
     * Download the specified backup.
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function download(Backup $backup)
    {
        if (! $backup->isCompleted()) {
            return $this->notFound('Backup');
        }

        $path = \Storage::disk($backup->disk)->path((string) $backup->path);

        if (! file_exists($path)) {
            return $this->notFound('Backup file');
        }

        return response()->download($path, $backup->name.'.sql');
    }

    /**
     * Get backup statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->backupService->getBackupStats();

        return $this->success($stats, 'Backup statistics retrieved successfully');
    }

    /**
     * Get or update backup schedule.
     */
    public function schedule(Request $request): JsonResponse
    {
        if ($request->isMethod('GET')) {
            $schedule = $this->backupService->getScheduleSettings();

            return $this->success($schedule, 'Backup schedule retrieved successfully');
        }

        // POST - update schedule
        $validated = $request->validate([
            'backup_schedule_enabled' => 'sometimes|boolean',
            'backup_schedule_frequency' => 'sometimes|in:daily,weekly,monthly',
            'backup_schedule_time' => 'sometimes|date_format:H:i',
            'backup_retention_days' => 'sometimes|integer|min:1|max:365',
            'backup_max_count' => 'sometimes|integer|min:1|max:100',
        ]);

        $schedule = $this->backupService->updateScheduleSettings($validated);

        return $this->success($schedule, 'Backup schedule updated successfully');
    }

    /**
     * Cleanup old backups.
     */
    public function cleanup(Request $request): JsonResponse
    {
        $retentionDaysRaw = $request->input('retention_days', 30);
        $retentionDays = is_numeric($retentionDaysRaw) ? (int) $retentionDaysRaw : 30;
        $maxBackupsRaw = $request->input('max_backups', 10);
        $maxBackups = is_numeric($maxBackupsRaw) ? (int) $maxBackupsRaw : 10;

        $deleted = $this->backupService->cleanupOldBackups($retentionDays, $maxBackups);
        $deletedStr = (string) $deleted;

        return $this->success([
            'deleted' => $deleted,
        ], "{$deletedStr} old backups cleaned up");
    }
}
