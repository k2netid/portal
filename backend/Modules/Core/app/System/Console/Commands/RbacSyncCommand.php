<?php

declare(strict_types=1);

namespace Modules\Core\System\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\System\Models\Role;
use Modules\Core\System\Services\CapabilityRegistryService;

class RbacSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:sync 
                            {--reset-defaults : Reset roles to manifest-declared default permissions}
                            {--role= : Specific role name to reset to defaults}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover and synchronize module capabilities into RBAC permissions and default roles';

    /**
     * Execute the console command.
     */
    public function handle(CapabilityRegistryService $registry): int
    {
        $this->info('Scanning module manifests and discovering capabilities...');

        $tree = $registry->getRegistryTree();
        $this->line('Discovered '.count($tree).' module(s) with declared capabilities:');

        $totalActions = 0;
        foreach ($tree as $module) {
            $actionsCount = 0;
            foreach ($module['features'] as $f) {
                $actionsCount += count($f['actions']);
            }
            $totalActions += $actionsCount;
            $this->line(" - [{$module['slug']}] {$module['name']}: ".count($module['features'])." feature(s), {$actionsCount} action(s)");
        }

        // 1. Sync permissions to database
        $this->info('Synchronizing declared permissions to database...');
        $result = $registry->syncToDatabase();
        $this->info("Permissions synchronized. Total: {$result['total']}, Newly created: ".count($result['created']));

        if (! empty($result['created'])) {
            foreach ($result['created'] as $newPerm) {
                $this->line("  + Created permission: {$newPerm}");
            }
        }

        // 2. Reset defaults if requested
        $specificRole = $this->option('role');
        $resetDefaults = (bool) $this->option('reset-defaults');

        if ($specificRole !== null && $specificRole !== '') {
            $roleName = (string) $specificRole;
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if (! $role) {
                $this->error("Role [{$roleName}] not found.");
                return self::FAILURE;
            }

            $this->info("Resetting role [{$roleName}] to manifest-declared defaults...");
            $synced = $registry->resetRoleToDefaults($role);
            $this->info("Role [{$roleName}] reset successfully with ".count($synced).' permissions.');
        } elseif ($resetDefaults) {
            $this->info('Resetting all standard roles to manifest-declared defaults...');
            $summary = $registry->resetAllRolesToDefaults();
            foreach ($summary as $roleName => $count) {
                $this->line(" - Role [{$roleName}]: {$count} permissions assigned.");
            }
            $this->info('All standard roles reset successfully.');
        } else {
            $this->comment('Tip: Use --reset-defaults to reset all role permissions to manifest defaults, or --role=<name> for a specific role.');
        }

        return self::SUCCESS;
    }
}
