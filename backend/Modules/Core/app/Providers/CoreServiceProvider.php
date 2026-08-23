<?php

declare(strict_types=1);

namespace Modules\Core\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Infra\Console\Commands\CreateBackup;
use Modules\Core\Providers\Concerns\RegistersMacroTierResources;
use Modules\Core\Security\Console\Commands\CleanupCspReports;
use Modules\Core\Security\Console\Commands\CleanupSecurityLogs;
use Modules\Core\Security\Console\Commands\ClearBlockedIps;
use Modules\Core\Security\Console\Commands\ClearRateLimit;
use Modules\Core\Security\Console\Commands\SecurityAuditDependencies;
use Modules\Core\Security\Console\Commands\SecurityMaintenance;
use Modules\Core\Security\Console\Commands\UpdateCloudflareIps;
use Modules\Core\Security\Services\AbacEvaluator;
use Modules\Core\System\Contracts\StorageQuotaServiceInterface;
use Modules\Core\System\Http\Middleware\EnsureExtensionActive;
use Modules\Core\System\Http\Middleware\EnsureKycLevel;
use Modules\Core\System\Providers\SystemServiceProvider;
use Modules\Core\System\Services\LocalStorageQuotaService;

class CoreServiceProvider extends ServiceProvider
{
    use RegistersMacroTierResources;

    protected string $name = 'Core';

    protected string $nameLower = 'core';

    /** @var array<int, string> */
    private const SUBMODULES = ['system', 'security', 'infra'];

    public function boot(): void
    {
        // Enforce eager loading globally in non-production environments to capture N+1 query anomalies
        Model::preventLazyLoading(! $this->app->isProduction());

        $tierRoot = dirname(__DIR__, 2);

        $this->registerMacroTierConfig($tierRoot, self::SUBMODULES);
        $this->registerMacroTierRoutes($tierRoot, self::SUBMODULES);
        $this->loadMigrationsFrom($tierRoot.'/database/migrations');
        $this->registerMacroTierViews($tierRoot, self::SUBMODULES);

        $this->app->register(SystemServiceProvider::class);

        $this->registerTierConsoleCommands([
            CreateBackup::class,
            CleanupCspReports::class,
            UpdateCloudflareIps::class,
            SecurityAuditDependencies::class,
            ClearBlockedIps::class,
            ClearRateLimit::class,
            CleanupSecurityLogs::class,
            SecurityMaintenance::class,
        ]);

        Route::aliasMiddleware('kyc', EnsureKycLevel::class);
        Route::aliasMiddleware('extension.active', EnsureExtensionActive::class);

        // ABAC Global Interceptor
        Gate::after(function ($user, $ability, $result, $arguments) {
            // ABAC only applies when RBAC/Spatie already granted the ability.
            if ($result !== true) {
                return $result;
            }

            // Extract resource name if an object is passed
            $targetResource = null;
            if (isset($arguments[0]) && is_object($arguments[0])) {
                $targetResource = get_class($arguments[0]);
            } elseif (isset($arguments[0]) && is_string($arguments[0])) {
                $targetResource = $arguments[0];
            }

            $evaluator = app(AbacEvaluator::class);

            return $evaluator->evaluate($user, $ability, $targetResource);
        });
    }

    public function register(): void
    {
        $this->app->singleton(
            StorageQuotaServiceInterface::class,
            LocalStorageQuotaService::class
        );
    }
}
