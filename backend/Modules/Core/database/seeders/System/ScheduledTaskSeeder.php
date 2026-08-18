<?php

namespace Modules\Core\System\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\System\Models\ScheduledTask;

class ScheduledTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // The "Golden Set" of scheduled tasks
        $tasks = [
            // Backup
            ['name' => 'Run Daily Backup', 'command' => 'system:backup', 'schedule' => '0 1 * * *', 'is_active' => true],

            // Queue & Jobs
            ['name' => 'Prune Failed Jobs', 'command' => 'queue:prune-failed', 'schedule' => '0 2 * * *', 'is_active' => true],

            // Media
            ['name' => 'Cleanup Temp Files', 'command' => 'media:cleanup-temp', 'schedule' => '0 3 * * *', 'is_active' => true],

            // Logs & Analytics
            ['name' => 'Cleanup System Logs', 'command' => 'logs:cleanup', 'schedule' => '0 0 * * 1', 'is_active' => true],
            ['name' => 'Cleanup Analytics', 'command' => 'analytics:cleanup', 'schedule' => '0 0 1 * *', 'is_active' => true],
            ['name' => 'Cleanup Slow Query Logs', 'command' => 'logs:cleanup-slow-queries', 'schedule' => '0 0 * * 2', 'is_active' => true],
            ['name' => 'Cleanup CSP Reports', 'command' => 'logs:cleanup-csp-reports', 'schedule' => '0 0 * * 3', 'is_active' => true],

            // Security
            ['name' => 'Update Cloudflare IPs', 'command' => 'security:update-cf-ips', 'schedule' => '0 4 * * *', 'is_active' => true],
            ['name' => 'Prune Revoked Tokens', 'command' => 'sanctum:prune-expired', 'schedule' => '0 5 * * *', 'is_active' => true],

            // System
            ['name' => 'System Optimization', 'command' => 'optimize', 'schedule' => '0 6 * * *', 'is_active' => true],
        ];

        foreach ($tasks as $task) {
            ScheduledTask::updateOrCreate(
                ['command' => $task['command']],
                $task
            );
        }

        $this->command->info('Scheduled tasks seeded successfully.');
    }
}
