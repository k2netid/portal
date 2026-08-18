<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Modules\Core\System\Http\Middleware\LazyExtensionBootMiddleware;
use Modules\Core\System\Registries\HookRegistry;
use Tests\TestCase;

class LazyActivationTest extends TestCase
{
    /**
     * Test that LazyExtensionBootMiddleware boots the extension provider on route match.
     */
    public function test_lazy_activation_middleware_boots_provider_on_route_match(): void
    {
        $studlyName = 'LazyMockPluginRoute';
        $providerClass = "Extensions\\{$studlyName}\\{$studlyName}ServiceProvider";
        $controllerClass = "Extensions\\{$studlyName}\\Http\\Controllers\\MockController";

        if (! class_exists($providerClass)) {
            eval("
                namespace Extensions\\{$studlyName};
                class {$studlyName}ServiceProvider extends \Illuminate\Support\ServiceProvider {
                    public static bool \$booted = false;
                    public function boot(): void {
                        self::\$booted = true;
                    }
                }
            ");
        }

        if (! class_exists($controllerClass)) {
            eval("
                namespace Extensions\\{$studlyName}\\Http\\Controllers;
                class MockController extends \Illuminate\Routing\Controller {
                    public function index() {
                        return response()->json(['success' => true]);
                    }
                }
            ");
        }

        // Reset boot flag
        $providerClass::$booted = false;
        $this->assertFalse($providerClass::$booted);

        // Register a temporary test route
        Route::middleware(LazyExtensionBootMiddleware::class)->get('/api/test-lazy-route-temp', [
            $controllerClass, 'index',
        ]);

        // Hit the route - the middleware should trigger dynamic class loading and register the provider
        $response = $this->get('/api/test-lazy-route-temp');
        $response->assertStatus(200);

        $this->assertTrue($providerClass::$booted);
    }

    /**
     * Test that HookRegistry dynamically boots the extension provider before executing callbacks.
     */
    public function test_lazy_activation_hook_boots_provider_on_callback(): void
    {
        $studlyName = 'LazyMockPluginHook';
        $providerClass = "Extensions\\{$studlyName}\\{$studlyName}ServiceProvider";

        if (! class_exists($providerClass)) {
            eval("
                namespace Extensions\\{$studlyName};
                class {$studlyName}ServiceProvider extends \Illuminate\Support\ServiceProvider {
                    public static bool \$booted = false;
                    public function boot(): void {
                        self::\$booted = true;
                    }
                }
            ");
        }

        // Reset boot flag
        $providerClass::$booted = false;
        $this->assertFalse($providerClass::$booted);

        /** @var HookRegistry $registry */
        $registry = app(HookRegistry::class);

        // Listen with a callback string that points to the extension
        $registry->listen('test_lazy_hook_event', "Extensions\\{$studlyName}\\Listeners\\MockListener@handle");

        // Fire the hook - registry should lazy boot the provider
        $registry->action('test_lazy_hook_event');

        $this->assertTrue($providerClass::$booted);
    }
}
