<?php

declare(strict_types=1);

namespace Modules\Core\System\Providers;

use Composer\Autoload\ClassLoader;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Support\ExtensionPaths;

class ExtensionAutoloadServiceProvider extends ServiceProvider
{
    /**
     * List of cached active extensions loaded in current request lifecycle.
     *
     * @var array<int, array{slug: string, type: string}>|null
     */
    protected ?array $activeExtensions = null;

    /**
     * Register services.
     */
    private static bool $runtimeBooted = false;

    public function register(): void
    {
        // Fail-safe check to prevent breaking Artisan CLI during early boot or installation phase
        try {
            if (! app()->runningInConsole() || Schema::hasTable('sys_extensions')) {
                $this->autoloadActiveExtensions();
                $this->registerActivePluginRoutes();
            }
        } catch (\Throwable $e) {
            // Fail silently to keep application bootable during migrations or schema setups
        }
    }

    public function boot(): void
    {
        $this->app->booted(function (): void {
            if ($this->shouldBootExtensionProviders()) {
                $this->bootExtensionRuntime();
            }
        });
    }

    public function bootExtensionRuntime(): void
    {
        if (self::$runtimeBooted) {
            return;
        }
        self::$runtimeBooted = true;
        $this->registerActiveExtensionProviders();
    }

    protected function shouldBootExtensionProviders(): bool
    {
        if (app()->runningInConsole() && ! request()) {
            return true;
        }

        $request = request();
        if ($request === null) {
            return true;
        }

        $path = $request->path();

        if (str_starts_with($path, 'api/v1/manage/')) {
            if (str_contains($path, 'extensions') || str_contains($path, 'extension')) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Get or fetch active extensions.
     *
     * @return array<int, array{slug: string, type: string}>
     */
    protected function getActiveExtensions(): array
    {
        if ($this->activeExtensions !== null) {
            return $this->activeExtensions;
        }

        $cacheFile = storage_path('framework/cache/active_extensions.json');

        if (file_exists($cacheFile)) {
            $content = @file_get_contents($cacheFile);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    /** @var array<int, array{slug: string, type: string}> $decoded */
                    $this->activeExtensions = $decoded;
                }
            }
        }

        if ($this->activeExtensions === null) {
            try {
                /** @var array<int, array{slug: string, type: string}> $extensions */
                $extensions = Extension::where('status', 'active')
                    ->get(['slug', 'type'])
                    ->toArray();

                $this->activeExtensions = $extensions;
                @file_put_contents($cacheFile, json_encode($this->activeExtensions));
            } catch (\Throwable $e) {
                $this->activeExtensions = [];
            }
        }

        return $this->activeExtensions;
    }

    /**
     * Autoload namespaces for all active modules/plugins dynamically.
     */
    protected function autoloadActiveExtensions(): void
    {
        $activeExtensions = $this->getActiveExtensions();

        if (empty($activeExtensions)) {
            return;
        }

        $loader = new ClassLoader;

        foreach ($activeExtensions as $ext) {
            $slug = $ext['slug'];
            $type = $ext['type'];

            // Class namespace formatting: e.g. "Extensions\WhatsAppGateway\"
            $namespace = 'Extensions\\'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))).'\\';

            // Physical path of the extension src folder (e.g. extensions/my-plugin/src)
            $path = $type === 'module'
                ? base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))).'/app')
                : ExtensionPaths::pluginSrcDirectory($slug);

            if (is_dir($path)) {
                $loader->addPsr4($namespace, $path);
            }
        }

        $loader->register();
    }

    /**
     * Register service providers for active plugins dynamically.
     */
    protected function registerActiveExtensionProviders(): void
    {
        $activeExtensions = $this->getActiveExtensions();

        if (empty($activeExtensions)) {
            return;
        }

        foreach ($activeExtensions as $ext) {
            // Static local modules are already registered in bootstrap/providers.php.
            // Dynamic plugins under extensions/ need dynamic ServiceProvider booting.
            if (($ext['type'] ?? '') !== 'plugin') {
                continue;
            }

            $slug = $ext['slug'];
            $studlyName = str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));

            // Expected ServiceProvider pattern: Extensions\TelegramAlerts\TelegramAlertsServiceProvider
            $providerClass = "Extensions\\{$studlyName}\\{$studlyName}ServiceProvider";

            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Map static route contributions for all active plugins.
     */
    protected function registerActivePluginRoutes(): void
    {
        $activeExtensions = $this->getActiveExtensions();
        if (empty($activeExtensions)) {
            return;
        }

        foreach ($activeExtensions as $ext) {
            if (($ext['type'] ?? '') !== 'plugin') {
                continue;
            }

            $slug = $ext['slug'];
            $manifestPath = ExtensionPaths::pluginManifestPath($slug);

            if (file_exists($manifestPath)) {
                $content = @file_get_contents($manifestPath);
                if ($content) {
                    $manifest = json_decode($content, true);
                    if (is_array($manifest)) {
                        $contributionPoints = $manifest['contribution_points'] ?? null;
                        $contributes = $manifest['contributes'] ?? null;

                        $routes = null;
                        if (is_array($contributionPoints)) {
                            $routes = $contributionPoints['routes'] ?? null;
                        }
                        if (! is_array($routes) && is_array($contributes)) {
                            $routes = $contributes['routes'] ?? null;
                        }

                        if (is_array($routes)) {
                            foreach ($routes as $route) {
                                if (is_array($route)) {
                                    $methodVal = $route['method'] ?? null;
                                    $uriVal = $route['uri'] ?? null;
                                    $actionVal = $route['action'] ?? null;

                                    $method = strtoupper(is_scalar($methodVal) ? (string) $methodVal : 'GET');
                                    $uri = is_scalar($uriVal) ? (string) $uriVal : '';
                                    $action = is_scalar($actionVal) ? (string) $actionVal : '';

                                    if ($uri !== '' && $action !== '') {
                                        $uriClean = ltrim($uri, '/');
                                        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'])) {
                                            Route::match([$method], $uriClean, $action);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
