<?php

namespace Modules\Core\System\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use Laravel\Sanctum\Console\Commands\PruneExpired;
use Modules\Core\System\Console\Commands\ApplyInstallProfileCommand;
use Modules\Core\System\Console\Commands\CleanupOldLogs;
use Modules\Core\System\Console\Commands\DynamicOpenApiExport;
use Modules\Core\System\Console\Commands\LicenseCheckCommand;
use Modules\Core\System\Console\Commands\SystemAudit;
use Modules\Core\System\Console\Commands\SystemClearCache;
use Modules\Core\System\Console\Commands\SystemHealthCheck;
use Modules\Core\System\Contracts\EmailTemplateRendererPortInterface;
use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Modules\Core\System\Contracts\OutboundWebhookPortInterface;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Facades\SandboxStorage;
use Modules\Core\System\Http\Controllers\Console\DashboardController;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Registries\DashboardRegistry;
use Modules\Core\System\Registries\HookRegistry;
use Modules\Core\System\Registries\LayoutRegistry;
use Modules\Core\System\Services\EmailTemplateRenderer;
use Modules\Core\System\Services\ModuleHealthProbe;
use Modules\Core\System\Services\OutboundWebhookDispatcher;
use Modules\Core\System\Services\PermissionRegistry;
use Modules\Core\System\Services\SandboxStorage as SandboxStorageService;

class SystemServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Super Admin Gate
        Gate::before(fn ($user, $capability) => $user->hasRole('super') ? true : null);

        // Register Request Macro for CSP Nonce
        Request::macro('cspNonce', function () {
            if (! $this->has('__csp_nonce')) {
                $this->attributes->set('__csp_nonce', Str::random(32));
            }

            return $this->attributes->get('__csp_nonce');
        });

        $this->app->booted(function (): void {
            if ($this->app->bound(DashboardRegistry::class)) {
                $registry = $this->app->make(DashboardRegistry::class);

                // Register Media Stats
                $registry->register('media', [
                    'title' => 'Media Library',
                    'component' => 'MediaStatsWidget',
                    'width' => '1/2',
                    'data_callback' => [DashboardController::class, 'getMediaStats'],
                ]);

                // Register User Stats
                $registry->register('users', [
                    'title' => 'User Management',
                    'component' => 'UserStatsWidget',
                    'width' => '1/2',
                    'data_callback' => [DashboardController::class, 'getUserStats'],
                ]);
            }
        });

        Passport::authorizationView(function (array $parameters) {
            /** @var Client $client */
            $client = $parameters['client'];
            $authToken = $parameters['authToken'];
            $scopesRaw = $parameters['scopes'] ?? [];
            $scopesInput = is_array($scopesRaw) ? $scopesRaw : [];
            $scopeIds = collect($scopesInput)
                ->map(function (mixed $scope): string {
                    if (is_object($scope) && property_exists($scope, 'id')) {
                        $id = $scope->id;

                        return is_string($id) || is_numeric($id) ? (string) $id : '';
                    }

                    return is_string($scope) || is_numeric($scope) ? (string) $scope : '';
                })
                ->filter()
                ->values()
                ->all();

            return redirect()->to('/'.Setting::resolveConsoleDashboardSlug().'/oauth/consent?'.http_build_query([
                'auth_token' => $authToken,
                'client' => $client->name,
                'scopes' => implode(' ', $scopeIds),
            ]));
        });

        // Register these commands globally so they are available when triggered
        // via HTTP (e.g. from ScheduledTaskController API).
        $this->commands([
            CleanupOldLogs::class,
            DynamicOpenApiExport::class,
            SystemAudit::class,
            PruneExpired::class,
            SystemClearCache::class,
            SystemHealthCheck::class,
            LicenseCheckCommand::class,
            ApplyInstallProfileCommand::class,
        ]);
    }

    public function register(): void
    {
        $this->app->singleton(OutboundWebhookPortInterface::class, OutboundWebhookDispatcher::class);
        $this->app->singleton(EmailTemplateRendererPortInterface::class, EmailTemplateRenderer::class);
        $this->app->singleton(PermissionRegistry::class);
        $this->app->singleton(ModuleHealthProbe::class);
        $this->app->singleton(DashboardRegistry::class);
        $this->app->singleton(\Modules\Core\System\Services\DashboardRegistry::class);
        $this->app->singleton(HookRegistry::class);
        $this->app->singleton(SandboxStorageService::class);
        $this->app->singleton(LayoutRegistryInterface::class, LayoutRegistry::class);

        // Register Global Hook and Sandbox Facade Aliases
        if (class_exists(AliasLoader::class)) {
            AliasLoader::getInstance()->alias('Hook', Hook::class);
            AliasLoader::getInstance()->alias('SandboxStorage', SandboxStorage::class);
        }
    }
}

if (! function_exists('csp_nonce')) {
    function csp_nonce(): string
    {
        return request()->cspNonce();
    }
}
