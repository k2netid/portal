<?php

declare(strict_types=1);

namespace Modules\Core\Providers\Concerns;

use Illuminate\Support\Facades\Route;

/**
 * Shared boot helpers for macro-tier module service providers (Core, Content, Intelligence).
 */
trait RegistersMacroTierResources
{
    /**
     * @param  array<int, string>  $submodules
     */
    protected function registerMacroTierConfig(string $tierRoot, array $submodules): void
    {
        foreach ($submodules as $sub) {
            $configPath = "{$tierRoot}/config/{$sub}.php";
            if (file_exists($configPath)) {
                $this->mergeConfigFrom($configPath, $sub);
            }
        }
    }

    /**
     * @param  array<int, string>  $submodules
     */
    protected function registerMacroTierRoutes(string $tierRoot, array $submodules): void
    {
        foreach ($submodules as $sub) {
            $api = "{$tierRoot}/routes/{$sub}_api.php";
            if (file_exists($api)) {
                Route::middleware('api')->prefix('api')->group($api);
            }
            $web = "{$tierRoot}/routes/{$sub}_web.php";
            if (file_exists($web)) {
                Route::middleware('web')->group($web);
            }
        }
    }

    /**
     * @param  array<int, string>  $submodules
     */
    protected function registerMacroTierViews(string $tierRoot, array $submodules): void
    {
        foreach ($submodules as $sub) {
            $views = "{$tierRoot}/resources/views/{$sub}";
            if (is_dir($views)) {
                $this->loadViewsFrom($views, $sub);
            }
        }
    }

    /**
     * @param  array<int, class-string>  $commandClasses
     */
    protected function registerTierConsoleCommands(array $commandClasses): void
    {
        if ($commandClasses === []) {
            return;
        }

        $this->commands($commandClasses);
    }
}
