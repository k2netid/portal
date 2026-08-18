<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\ExtensionPaths;
use Tests\TestCase;

class DeveloperKitScaffolderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected string $localPluginSlug = 'scaffolded-local-plugin';

    protected string $downloadPluginSlug = 'scaffolded-download-plugin';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();

        // Clear directories if leftovers exist
        $this->cleanUpScaffolds();
    }

    protected function tearDown(): void
    {
        $this->cleanUpScaffolds();

        parent::tearDown();
    }

    protected function cleanUpScaffolds(): void
    {
        foreach ([$this->localPluginSlug, $this->downloadPluginSlug] as $slug) {
            $path = ExtensionPaths::pluginDirectory($slug);
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
        }

        $scaffoldTemp = storage_path('app/scaffolds');
        if (File::isDirectory($scaffoldTemp)) {
            File::deleteDirectory($scaffoldTemp);
        }
    }

    /**
     * Test visual scaffolding with the install_locally option set to true.
     */
    public function test_scaffolder_local_installation(): void
    {
        $payload = [
            'name' => 'Visual Scaffolded Plugin',
            'slug' => $this->localPluginSlug,
            'author' => 'DeveloperKit',
            'version' => '1.0.0',
            'description' => 'A beautiful scaffolded local plugin.',
            'install_locally' => true,
            'routes' => [
                [
                    'method' => 'GET',
                    'uri' => '/api/v1/scaffolded-local-route',
                    'action' => 'MyTestController@index',
                ],
            ],
            'sidebar_menu' => [
                [
                    'id' => 'scaf-item',
                    'title' => 'Scaffolded Item',
                    'icon' => 'star',
                    'group' => 'content',
                    'route' => '/dashboard/scaffolded',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/cck/scaffold', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Plugin scaffolded and installed locally successfully',
        ]);

        $pluginDir = ExtensionPaths::pluginDirectory($this->localPluginSlug);
        $this->assertTrue(File::isDirectory($pluginDir));
        $this->assertTrue(File::exists("{$pluginDir}/manifest.json"));
        $this->assertTrue(File::exists("{$pluginDir}/src/Providers/PluginServiceProvider.php"));
        $this->assertTrue(File::exists("{$pluginDir}/src/Http/Controllers/MyTestController.php"));

        // Verify manifest content
        $manifest = json_decode(File::get("{$pluginDir}/manifest.json"), true);
        $this->assertEquals($this->localPluginSlug, $manifest['slug']);
        $this->assertEquals('Visual Scaffolded Plugin', $manifest['name']);
        $this->assertCount(1, $manifest['contribution_points']['routes']);
        $this->assertCount(1, $manifest['contribution_points']['sidebar_menu']);
    }

    /**
     * Test visual scaffolding returning a packaged ZIP binary download.
     */
    public function test_scaffolder_zip_download_generation(): void
    {
        $payload = [
            'name' => 'Packaged Scaffolded Plugin',
            'slug' => $this->downloadPluginSlug,
            'author' => 'DeveloperKit',
            'version' => '1.0.0',
            'description' => 'A packaged scaffolded plugin.',
            'install_locally' => false,
            'routes' => [
                [
                    'method' => 'GET',
                    'uri' => '/api/v1/scaffolded-dl-route',
                    'action' => 'DlController@show',
                ],
            ],
            'sidebar_menu' => [],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/cck/scaffold', $payload);

        // Verify that it is returned as a file download (ZIP binary response)
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
    }
}
