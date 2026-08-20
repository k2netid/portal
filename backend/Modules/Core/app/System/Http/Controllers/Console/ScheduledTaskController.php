<?php

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\ScheduledTask;
use Modules\Core\System\Models\User;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class ScheduledTaskController extends BaseApiController
{
    /**
     * List all scheduled tasks
     */
    public function index(Request $request): JsonResponse
    {
        $perPageInput = $request->input('per_page', 10);
        $perPage = is_numeric($perPageInput) ? (int) $perPageInput : 10;

        $tasks = ScheduledTask::orderBy('name')->paginate($perPage);

        return $this->paginated($tasks, 'Scheduled tasks retrieved successfully');
    }

    public function allowedCommands(): JsonResponse
    {
        return $this->success([
            'commands' => ScheduledTask::getAllowedCommands(),
            'prerequisites' => ScheduledTask::checkAllPrerequisites(),
            'base_path' => base_path(),
        ], 'Allowed commands and prerequisites retrieved');
    }

    /**
     * Perform bulk actions on scheduled tasks
     */
    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:activate,deactivate,run,delete',
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'required|string|uuid',
        ]);

        $action = (string) $validated['action'];
        /** @var array<string> $taskIds */
        $taskIds = $validated['task_ids'];

        $authUser = Auth::user();
        $user = $authUser instanceof User ? $authUser : null;
        if (! $user || ! $user->can('manage scheduled tasks')) {
            return $this->forbidden('Insufficient permissions context.');
        }

        $tasks = ScheduledTask::whereIn('id', $taskIds)->get();
        $affectedCount = 0;
        $runResults = [];

        switch ($action) {
            case 'activate':
                $affectedCount = ScheduledTask::whereIn('id', $taskIds)->update(['is_active' => true]);
                break;

            case 'deactivate':
                $affectedCount = ScheduledTask::whereIn('id', $taskIds)->update(['is_active' => false]);
                break;

            case 'delete':
                $affectedCount = ScheduledTask::whereIn('id', $taskIds)->delete();
                break;

            case 'run':
                app(Kernel::class)->bootstrap();
                foreach ($tasks as $task) {
                    if (! ScheduledTask::isCommandAllowed($task->command)) {
                        continue;
                    }
                    try {
                        $task->update(['status' => 'running', 'last_run_at' => now()]);
                        $exitCode = Artisan::call($task->command);
                        $output = Artisan::output();
                        if (in_array(trim($output), ['', '0'], true) && $exitCode === 0) {
                            $output = 'Task executed successfully.';
                        }
                        $status = $exitCode === 0 ? 'completed' : 'failed';
                        $task->update(['status' => $status, 'output' => $output]);
                        $runResults[$task->id] = ['name' => $task->name, 'status' => $status, 'exit_code' => $exitCode];
                        $affectedCount++;
                    } catch (\Throwable $e) {
                        $task->update(['status' => 'failed', 'output' => $e->getMessage()]);
                        $runResults[$task->id] = ['name' => $task->name, 'status' => 'failed', 'error' => $e->getMessage()];
                    }
                }
                break;
        }

        $countInt = is_numeric($affectedCount) ? (int) $affectedCount : count($taskIds);

        Log::info("Scheduled tasks bulk action: {$action}", [
            'count' => $countInt,
            'user_id' => Auth::id(),
        ]);

        return $this->success([
            'action' => $action,
            'affected_count' => $countInt,
            'run_results' => $runResults,
        ], "Bulk action '{$action}' executed successfully on {$countInt} tasks");
    }

    /**
     * Apply preset or reset defaults
     */
    public function applyPreset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preset' => 'required|string|in:recommended,reset_defaults,all_active,all_inactive',
        ]);

        $preset = (string) $validated['preset'];

        $authUser = Auth::user();
        $user = $authUser instanceof User ? $authUser : null;
        if (! $user || ! $user->can('manage scheduled tasks')) {
            return $this->forbidden('Insufficient permissions context.');
        }

        $catalog = ScheduledTask::getCommandCatalog();

        if ($preset === 'reset_defaults') {
            $seeder = new \Modules\Core\System\Database\Seeders\ScheduledTaskSeeder;
            $seeder->run();

            return $this->success(null, 'Scheduled tasks reset to default golden set successfully');
        }

        if ($preset === 'recommended') {
            foreach ($catalog as $cmd => $meta) {
                $isRec = $meta['is_recommended'];
                /** @var ScheduledTask|null $existing */
                $existing = ScheduledTask::where('command', $cmd)->first();
                if ($existing) {
                    $existing->update([
                        'is_active' => $isRec,
                        'schedule' => $meta['default_schedule'],
                    ]);
                } elseif ($isRec) {
                    ScheduledTask::create([
                        'name' => $meta['name'],
                        'command' => $cmd,
                        'schedule' => $meta['default_schedule'],
                        'description' => $meta['description'],
                        'is_active' => true,
                    ]);
                }
            }

            return $this->success(null, 'Recommended task preset applied successfully');
        }

        if ($preset === 'all_active') {
            ScheduledTask::query()->update(['is_active' => true]);

            return $this->success(null, 'All scheduled tasks activated');
        }

        if ($preset === 'all_inactive') {
            ScheduledTask::query()->update(['is_active' => false]);

            return $this->success(null, 'All scheduled tasks deactivated');
        }

        return $this->error('Unknown preset', 422);
    }

    /**
     * Create a new scheduled task
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'command' => 'required|string|max:500',
            'schedule' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
        ]);

        // Validate command against whitelist
        if (! ScheduledTask::isCommandAllowed($validated['command'])) {
            return $this->error(
                'Command not allowed. Use GET /scheduled-tasks/allowed-commands to see available commands.',
                403,
                ['allowed_commands' => ScheduledTask::ALLOWED_COMMANDS],
                'COMMAND_NOT_ALLOWED'
            );
        }

        // Validate cron expression
        if (! ScheduledTask::isValidCronExpression($validated['schedule'])) {
            return $this->validationError([
                'schedule' => ['Invalid cron expression format'],
            ]);
        }

        /** @var ScheduledTask $task */
        $task = ScheduledTask::create($validated);

        Log::info('Scheduled task created', [
            'task_id' => $task->id,
            'command' => $task->command,
            'user_id' => Auth::id(),
        ]);

        return $this->success($task, 'Scheduled task created successfully', 201);
    }

    /**
     * Update a scheduled task
     */
    public function update(Request $request, string $id): JsonResponse
    {
        /** @var ScheduledTask|null $task */
        $task = ScheduledTask::find($id);

        if (! $task) {
            return $this->notFound('Task');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'command' => 'sometimes|required|string|max:500',
            'schedule' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
        ]);

        // Validate command if changed
        if (isset($validated['command']) && ! ScheduledTask::isCommandAllowed($validated['command'])) {
            return $this->error(
                'Command not allowed',
                403,
                ['allowed_commands' => ScheduledTask::ALLOWED_COMMANDS],
                'COMMAND_NOT_ALLOWED'
            );
        }

        // Validate cron expression if changed
        if (isset($validated['schedule']) && ! ScheduledTask::isValidCronExpression($validated['schedule'])) {
            return $this->validationError([
                'schedule' => ['Invalid cron expression format'],
            ]);
        }

        $task->update($validated);

        Log::info('Scheduled task updated', [
            'task_id' => $task->id,
            'changes' => $validated,
            'user_id' => Auth::id(),
        ]);

        /** @var ScheduledTask $freshTask */
        $freshTask = $task->fresh();

        return $this->success($freshTask, 'Scheduled task updated successfully');
    }

    /**
     * Run a scheduled task manually
     */
    public function run(string $id): JsonResponse
    {
        /** @var ScheduledTask|null $task */
        $task = ScheduledTask::find($id);

        if (! $task) {
            return $this->notFound('Task');
        }

        $authUser = Auth::user();
        $user = $authUser instanceof User ? $authUser : null;

        // Check if user has permission
        if (! $user || ! $user->can('manage scheduled tasks')) {
            Log::warning('Unauthorized task execution attempt', [
                'task_id' => $task->id,
                'command' => $task->command,
                'user_id' => Auth::id(),
            ]);

            return $this->error(
                'Insufficient permissions to execute scheduled tasks',
                403,
                [],
                'INSUFFICIENT_PERMISSIONS'
            );
        }

        // Double-check command is still allowed (in case whitelist changed)
        if (! ScheduledTask::isCommandAllowed($task->command)) {
            return $this->error(
                'This command is no longer allowed to be executed',
                403,
                [],
                'COMMAND_NOT_ALLOWED'
            );
        }

        try {
            $task->update([
                'status' => 'running',
                'last_run_at' => now(),
            ]);

            Log::info('Executing scheduled task', [
                'task_id' => $task->id,
                'command' => $task->command,
                'user_id' => Auth::id(),
            ]);

            // Ensure console commands are loaded
            app(Kernel::class)->bootstrap();

            // Execute the command
            /** @var int $exitCode */
            $exitCode = Artisan::call($task->command);
            /** @var string $output */
            $output = Artisan::output();

            if (in_array(trim($output), ['', '0'], true) && $exitCode === 0) {
                $output = 'Task executed successfully (No output generated).';
            }

            $status = $exitCode === 0 ? 'completed' : 'failed';

            $task->update([
                'status' => $status,
                'output' => $output,
            ]);

            Log::info('Scheduled task completed', [
                'task_id' => $task->id,
                'status' => $status,
                'exit_code' => $exitCode,
            ]);

            /** @var ScheduledTask $freshTask */
            $freshTask = $task->fresh();

            return $this->success([
                'task' => $freshTask,
                'output' => $output,
                'exit_code' => $exitCode,
            ], 'Task executed successfully');

        } catch (CommandNotFoundException) {
            $task->update([
                'status' => 'failed',
                'output' => 'Command not found on server.',
            ]);

            Log::error('Scheduled task command not found', [
                'task_id' => $task->id,
                'command' => $task->command,
            ]);

            return $this->error(
                'Command not found. The required package might be missing.',
                422,
                [],
                'COMMAND_NOT_FOUND'
            );
        } catch (\Throwable $e) {
            $task->update([
                'status' => 'failed',
                'output' => $e->getMessage(),
            ]);

            Log::error('Scheduled task execution failed', [
                'task_id' => $task->id,
                'command' => $task->command,
                'error' => $e->getMessage(),
            ]);

            return $this->error(
                'Task execution failed: '.$e->getMessage(),
                500,
                [],
                'TASK_EXECUTION_ERROR'
            );
        }
    }

    /**
     * Delete a scheduled task
     */
    public function destroy(string $id): JsonResponse
    {
        /** @var ScheduledTask|null $task */
        $task = ScheduledTask::find($id);

        if (! $task) {
            return $this->notFound('Task');
        }

        Log::info('Scheduled task deleted', [
            'task_id' => $task->id,
            'command' => $task->command,
            'user_id' => Auth::id(),
        ]);

        $task->delete();

        return $this->success(null, 'Task deleted successfully');
    }

    /**
     * Get task execution history
     */
    public function show(string $id): JsonResponse
    {
        /** @var ScheduledTask|null $task */
        $task = ScheduledTask::find($id);

        if (! $task) {
            return $this->notFound('Task');
        }

        return $this->success([
            'task' => $task,
            'next_run_at' => $task->getNextRunAt()?->format('Y-m-d H:i:s'),
        ], 'Task details retrieved');
    }

    /**
     * Run an ad-hoc command without creating a permanent task record
     */
    public function runAdhoc(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command' => 'required|string|max:500',
            'parameters' => 'nullable|string|max:500',
        ]);

        $fullCommand = $validated['parameters']
            ? "{$validated['command']} {$validated['parameters']}"
            : $validated['command'];

        // Security check
        if (! ScheduledTask::isCommandAllowed($validated['command'])) {
            return $this->error('Command not allowed', 403, [], 'COMMAND_NOT_ALLOWED');
        }

        $authUser = Auth::user();
        $user = $authUser instanceof User ? $authUser : null;

        // Permission check
        if (! $user || ! $user->can('manage scheduled tasks')) {
            return $this->forbidden('Insufficient permissions context.');
        }

        try {
            Log::info('Executing ad-hoc command', [
                'command' => $fullCommand,
                'user_id' => Auth::id(),
            ]);

            // Ensure console commands are loaded
            app(Kernel::class)->bootstrap();

            /** @var int $exitCode */
            $exitCode = Artisan::call($fullCommand);
            /** @var string $output */
            $output = Artisan::output();

            return $this->success([
                'output' => $output,
                'exit_code' => $exitCode,
            ], 'Command executed successfully');
        } catch (\Throwable $e) {
            Log::error('Ad-hoc command execution failed', [
                'command' => $fullCommand,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Execution failed: '.$e->getMessage(), 500);
        }
    }
}
