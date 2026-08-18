<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\ExtensionLog;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\ExtensionPaths;
use Tests\TestCase;
use ZipArchive;

class ExtensionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected string $tempZipPath;

    protected string $evilZipPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();

        // Ensure database records are fresh
        Extension::truncate();
        ExtensionLog::truncate();
    }

    protected function tearDown(): void
    {
        foreach ([
            'test-uploaded-plugin',
            'malicious-plugin',
            'obfuscated-plugin',
            'filewrite-plugin',
            'inclusion-plugin',
        ] as $slug) {
            $dir = ExtensionPaths::pluginDirectory($slug);
            if (is_dir($dir)) {
                File::deleteDirectory($dir);
            }
        }

        if (isset($this->tempZipPath) && file_exists($this->tempZipPath)) {
            @unlink($this->tempZipPath);
        }
        if (isset($this->evilZipPath) && file_exists($this->evilZipPath)) {
            @unlink($this->evilZipPath);
        }

        parent::tearDown();
    }

    /**
     * Test admin can list all discovered extensions.
     */
    public function test_admin_can_list_extensions(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);

        // Auto-discovery registers core system modules automatically
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    /**
     * Test admin can activate and deactivate extensions.
     */
    public function test_admin_can_activate_and_deactivate_extension(): void
    {
        // 1. Create a dummy inactive plugin record in DB
        $ext = Extension::create([
            'slug' => 'demo-plugin',
            'type' => 'plugin',
            'name' => 'Demo Plugin',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        // 2. Activate
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/activate");

        $response->assertStatus(200);
        $this->assertEquals('active', $response->json('data.status'));
        $this->assertEquals('active', $ext->fresh()->status);

        // 3. Deactivate
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/deactivate");

        $response->assertStatus(200);
        $this->assertEquals('inactive', $response->json('data.status'));
        $this->assertEquals('inactive', $ext->fresh()->status);
    }

    /**
     * Test admin can configure extension settings.
     */
    public function test_admin_can_configure_settings(): void
    {
        $ext = Extension::create([
            'slug' => 'config-plugin',
            'type' => 'plugin',
            'name' => 'Config Plugin',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'settings' => [],
        ]);

        $settingsData = [
            'api_token' => 'secret-12345',
            'enabled_alerts' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/manage/infra/extensions/{$ext->slug}/settings", [
                'settings' => $settingsData,
            ]);

        $response->assertStatus(200);
        $this->assertEquals($settingsData, $response->json('data.settings'));
        $this->assertEquals($settingsData, $ext->fresh()->settings);
    }

    /**
     * Test dynamic ZIP upload, manifest checking, and registration.
     */
    public function test_admin_can_upload_valid_extension_zip(): void
    {
        $slug = 'test-uploaded-plugin';
        $existing = ExtensionPaths::pluginDirectory($slug);
        if (is_dir($existing)) {
            File::deleteDirectory($existing);
        }

        // 1. Construct a valid temporary ZIP in PHP
        $this->tempZipPath = tempnam(sys_get_temp_dir(), 'zip').'.zip';

        $zip = new ZipArchive;
        if ($zip->open($this->tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->fail('Failed to create temporary test ZIP file.');
        }

        $manifest = [
            'slug' => 'test-uploaded-plugin',
            'name' => 'Test Uploaded Plugin',
            'type' => 'plugin',
            'version' => '1.2.3',
            'author' => 'Jejakawan Team',
            'dependencies' => ['core' => '>=2.0.0'],
        ];

        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->addFromString('src/Main.php', '<?php namespace Extensions\TestUploadedPlugin; class Main {}');
        $zip->close();

        // 2. Wrap as UploadedFile
        $uploadedFile = new UploadedFile(
            $this->tempZipPath,
            'test-uploaded-plugin.zip',
            'application/zip',
            null,
            true
        );

        // 3. Post to upload endpoint
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/upload', [
                'file' => $uploadedFile,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.slug', 'test-uploaded-plugin');
        $response->assertJsonPath('data.version', '1.2.3');
        $response->assertJsonPath('data.status', 'inactive');

        // Check file was extracted and DB record exists
        $pluginDir = ExtensionPaths::pluginDirectory($slug);
        $this->assertTrue(is_dir($pluginDir));
        $this->assertTrue(file_exists($pluginDir.'/manifest.json'));
        $this->assertTrue(Extension::where('slug', $slug)->exists());
    }

    /**
     * Test that the Static Security Scanner blocks malicious ZIP packages containing shell executions.
     */
    public function test_security_scanner_blocks_malicious_php_functions(): void
    {
        // 1. Construct a malicious temporary ZIP in PHP containing backticks and shell_exec
        $this->evilZipPath = tempnam(sys_get_temp_dir(), 'zip').'.zip';

        $zip = new ZipArchive;
        if ($zip->open($this->evilZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->fail('Failed to create temporary evil ZIP file.');
        }

        $manifest = [
            'slug' => 'malicious-plugin',
            'name' => 'Malicious Plugin',
            'type' => 'plugin',
            'version' => '1.0.0',
        ];

        $zip->addFromString('manifest.json', json_encode($manifest));

        // Malicious file calling forbidden shell command
        $zip->addFromString('src/Backdoor.php', '<?php shell_exec("rm -rf /opt"); ?>');
        $zip->close();

        // 2. Wrap as UploadedFile
        $uploadedFile = new UploadedFile(
            $this->evilZipPath,
            'malicious-plugin.zip',
            'application/zip',
            null,
            true
        );

        // 3. Post to upload endpoint
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/upload', [
                'file' => $uploadedFile,
            ]);

        // 4. Assert it returns failure and mentions Security Gate block!
        $response->assertStatus(400);
        $this->assertStringContainsString('Security Gate', $response->json('message'));
        $this->assertStringContainsString('Backdoor.php', $response->json('message'));

        // Assert no directory was created and no DB record registered!
        $this->assertFalse(is_dir(ExtensionPaths::pluginDirectory('malicious-plugin')));
        $this->assertFalse(Extension::where('slug', 'malicious-plugin')->exists());
    }

    /**
     * Test that the Static Security Scanner blocks packages attempting call_user_func RCE obfuscations.
     */
    public function test_security_scanner_blocks_obfuscated_call_user_func(): void
    {
        $this->evilZipPath = tempnam(sys_get_temp_dir(), 'zip').'.zip';
        $zip = new ZipArchive;
        $zip->open($this->evilZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $manifest = [
            'slug' => 'obfuscated-plugin',
            'name' => 'Obfuscated Plugin',
            'type' => 'plugin',
            'version' => '1.0.0',
        ];
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->addFromString('src/Bypass.php', '<?php call_user_func("exec", "rm -rf /"); ?>');
        $zip->close();

        $uploadedFile = new UploadedFile($this->evilZipPath, 'obfuscated-plugin.zip', 'application/zip', null, true);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/upload', ['file' => $uploadedFile]);

        $response->assertStatus(400);
        $this->assertStringContainsString('call_user_func', $response->json('message'));
        $this->assertFalse(is_dir(ExtensionPaths::pluginDirectory('obfuscated-plugin')));
    }

    /**
     * Test that the Static Security Scanner blocks packages containing raw, un-sandboxed filesystem modifications.
     */
    public function test_security_scanner_blocks_raw_filesystem_mutations(): void
    {
        $this->evilZipPath = tempnam(sys_get_temp_dir(), 'zip').'.zip';
        $zip = new ZipArchive;
        $zip->open($this->evilZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $manifest = [
            'slug' => 'filewrite-plugin',
            'name' => 'File Write Plugin',
            'type' => 'plugin',
            'version' => '1.0.0',
        ];
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->addFromString('src/Hack.php', '<?php file_put_contents("/etc/passwd", "hack"); ?>');
        $zip->close();

        $uploadedFile = new UploadedFile($this->evilZipPath, 'filewrite-plugin.zip', 'application/zip', null, true);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/upload', ['file' => $uploadedFile]);

        $response->assertStatus(400);
        $this->assertStringContainsString('file_put_contents', $response->json('message'));
        $this->assertFalse(is_dir(ExtensionPaths::pluginDirectory('filewrite-plugin')));
    }

    /**
     * Test that the Static Security Scanner blocks packages with dynamic file inclusions.
     */
    public function test_security_scanner_blocks_dynamic_file_inclusion(): void
    {
        $this->evilZipPath = tempnam(sys_get_temp_dir(), 'zip').'.zip';
        $zip = new ZipArchive;
        $zip->open($this->evilZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $manifest = [
            'slug' => 'inclusion-plugin',
            'name' => 'Inclusion Plugin',
            'type' => 'plugin',
            'version' => '1.0.0',
        ];
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->addFromString('src/IncludeHack.php', '<?php include $dynamicPath; ?>');
        $zip->close();

        $uploadedFile = new UploadedFile($this->evilZipPath, 'inclusion-plugin.zip', 'application/zip', null, true);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/upload', ['file' => $uploadedFile]);

        $response->assertStatus(400);
        $this->assertStringContainsString('include/require', $response->json('message'));
        $this->assertFalse(is_dir(ExtensionPaths::pluginDirectory('inclusion-plugin')));
    }

    /**
     * Test dependency verification prevents activation of plugin if required plugin is not installed.
     */
    public function test_dependency_verification_prevents_activation_if_requirement_not_installed(): void
    {
        $ext = Extension::create([
            'slug' => 'dependent-plugin',
            'type' => 'plugin',
            'name' => 'Dependent Plugin',
            'version' => '1.0.0',
            'database_version' => '0.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => [
                'missing-plugin' => '>=1.0.0',
            ],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/activate");

        $response->assertStatus(400);
        $this->assertStringContainsString('tidak terpasang', $response->json('message'));
    }

    /**
     * Test dependency verification prevents activation of plugin if required plugin is installed but inactive.
     */
    public function test_dependency_verification_prevents_activation_if_requirement_inactive(): void
    {
        // Create required plugin (inactive)
        Extension::create([
            'slug' => 'required-plugin',
            'type' => 'plugin',
            'name' => 'Required Plugin',
            'version' => '1.0.0',
            'database_version' => '0.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $ext = Extension::create([
            'slug' => 'dependent-plugin',
            'type' => 'plugin',
            'name' => 'Dependent Plugin',
            'version' => '1.0.0',
            'database_version' => '0.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => [
                'required-plugin' => '>=1.0.0',
            ],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/activate");

        $response->assertStatus(400);
        $this->assertStringContainsString('belum diaktifkan', $response->json('message'));
    }

    /**
     * Test dependency verification prevents activation of plugin if required plugin has version conflict.
     */
    public function test_dependency_verification_prevents_activation_if_requirement_version_conflict(): void
    {
        // Create required plugin with version 1.0.0 (active)
        Extension::create([
            'slug' => 'required-plugin',
            'type' => 'plugin',
            'name' => 'Required Plugin',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);

        $ext = Extension::create([
            'slug' => 'dependent-plugin',
            'type' => 'plugin',
            'name' => 'Dependent Plugin',
            'version' => '1.0.0',
            'database_version' => '0.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => [
                'required-plugin' => '^2.0.0', // Requires >=2.0.0 and <3.0.0
            ],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/activate");

        $response->assertStatus(400);
        $this->assertStringContainsString('Konflik versi', $response->json('message'));
    }

    /**
     * Test admin can retrieve dynamic navigation items registered via Hook filter.
     */
    public function test_admin_can_retrieve_dynamic_navigation(): void
    {
        // Hook into sidebar_navigation and register a dynamic item
        Hook::listen('sidebar_navigation', function ($items) {
            $items[] = [
                'name' => 'dynamic-test',
                'label' => 'Dynamic Test Page',
                'icon' => 'activity',
                'group' => 'content',
            ];

            return $items;
        });

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions/navigation');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'dynamic-test');
        $response->assertJsonPath('data.0.label', 'Dynamic Test Page');
    }
}
