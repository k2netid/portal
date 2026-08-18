<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\User;
use Modules\Core\System\Providers\ExtensionAutoloadServiceProvider;
use Modules\Core\System\Support\ExtensionPaths;
use Tests\TestCase;

class MicrofrontendFederationTest extends TestCase
{
    use RefreshDatabase;

    protected string $pluginSlug = 'test-federation-plugin';

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();

        // Ensure database records are fresh
        Extension::truncate();

        // 1. Create a physical mock manifest.json for federation testing
        $pluginPath = ExtensionPaths::pluginDirectory($this->pluginSlug);
        if (! File::isDirectory($pluginPath)) {
            File::makeDirectory($pluginPath, 0755, true);
        }

        $manifest = [
            'slug' => $this->pluginSlug,
            'name' => 'Federation Test Plugin',
            'type' => 'plugin',
            'version' => '1.0.0',
            'author' => 'Jejakawan',
            'contribution_points' => [
                'routes' => [
                    [
                        'method' => 'GET',
                        'uri' => '/api/v1/federation-test-route',
                        'action' => 'Extensions\\TestFederationPlugin\\Http\\Controllers\\MockController@index',
                    ],
                ],
                'sidebar_menu' => [
                    [
                        'id' => 'fed-sidebar-item',
                        'title' => 'Federated UI Widget',
                        'icon' => 'globe',
                        'group' => 'content',
                        'route' => '/dashboard/federated',
                    ],
                ],
            ],
        ];

        File::put("{$pluginPath}/manifest.json", json_encode($manifest));

        // Create the active model in the database with the required database_version
        Extension::create([
            'slug' => $this->pluginSlug,
            'name' => 'Federation Test Plugin',
            'type' => 'plugin',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);
    }

    protected function tearDown(): void
    {
        // Cleanup the mocked plugin files
        $pluginPath = ExtensionPaths::pluginDirectory($this->pluginSlug);
        if (File::isDirectory($pluginPath)) {
            File::deleteDirectory($pluginPath);
        }

        parent::tearDown();
    }

    /**
     * Test that active plugins' sidebar menu contributions are returned statically from the navigation API.
     */
    public function test_navigation_api_returns_manifest_driven_sidebar_items(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions/navigation');

        $response->assertStatus(200);

        // Assert that our manifest-driven "Federated UI Widget" menu item is present in the hydration payload!
        $response->assertJsonFragment([
            'name' => 'fed-sidebar-item',
            'label' => 'Federated UI Widget',
            'icon' => 'globe',
            'group' => 'content',
            'to' => '/dashboard/federated',
        ]);
    }

    /**
     * Test that static route contributions are dynamically mapped and accessible on system boot.
     */
    public function test_routes_api_registers_manifest_driven_routes(): void
    {
        $controllerClass = 'Extensions\\TestFederationPlugin\\Http\\Controllers\\MockController';
        if (! class_exists($controllerClass)) {
            eval("
                namespace Extensions\\TestFederationPlugin\\Http\\Controllers;
                class MockController extends \Illuminate\Routing\Controller {
                    public function index() {
                        return response()->json(['success' => true, 'from' => 'federated-route']);
                    }
                }
            ");
        }

        // Re-register the autoload provider to trigger manifest scanning and static route registration
        app()->register(ExtensionAutoloadServiceProvider::class, true);

        // Hit the federated static route
        $response = $this->get('/api/v1/federation-test-route');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'from' => 'federated-route',
        ]);
    }
}
