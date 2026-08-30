<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\ConsoleMenu;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\ExtensionLog;
use Modules\Core\System\Models\Permission;
use Modules\Core\System\Models\Setting;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\ExtensionGraphService;
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

    /**
     * Core (Modules/Core alias) must discover as kernel: is_core + active.
     */
    public function test_core_module_is_discovered_as_active_kernel(): void
    {
        Extension::create([
            'slug' => 'core',
            'type' => 'module',
            'name' => 'Core',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);

        $core = collect($response->json('data'))->firstWhere('slug', 'core');
        $this->assertNotNull($core);
        $this->assertTrue((bool) $core['is_core']);
        $this->assertEquals('active', $core['status']);
    }

    /**
     * Kernel cannot be deactivated even when the DB row was corrupted.
     */
    public function test_kernel_slug_cannot_be_deactivated_even_if_is_core_flag_false(): void
    {
        $ext = Extension::create([
            'slug' => 'core',
            'type' => 'module',
            'name' => 'Core',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/deactivate");

        $response->assertStatus(400);
        $this->assertStringContainsString('kernel', strtolower((string) $response->json('message')));
        $this->assertEquals('active', $ext->fresh()->status);
    }

    /**
     * Kernel cannot be uninstalled even when the DB row was corrupted.
     */
    public function test_kernel_slug_cannot_be_uninstalled_even_if_is_core_flag_false(): void
    {
        $ext = Extension::create([
            'slug' => 'core',
            'type' => 'module',
            'name' => 'Core',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/manage/infra/extensions/{$ext->slug}/uninstall");

        $response->assertStatus(400);
        $this->assertStringContainsString('kernel', strtolower((string) $response->json('message')));
        $this->assertNotNull(Extension::where('slug', 'core')->first());
        $this->assertDirectoryExists(base_path('Modules/Core'));
    }

    public function test_first_party_in_tree_module_cannot_be_uninstalled(): void
    {
        $ext = Extension::create([
            'slug' => 'layout',
            'type' => 'module',
            'name' => 'Layout',
            'version' => '1.1.0',
            'database_version' => '1.1.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/manage/infra/extensions/{$ext->slug}/uninstall");

        $response->assertStatus(422);
        $this->assertStringContainsString('first-party', strtolower((string) $response->json('message')));
        $this->assertNotNull(Extension::where('slug', 'layout')->first());
        $this->assertDirectoryExists(base_path('Modules/Layout'));
    }

    /**
     * Mail rediscovery persists settings_route / license_tier and does not wipe requirements.
     */
    public function test_mail_discovery_syncs_manifest_contract_fields(): void
    {
        Extension::create([
            'slug' => 'mail',
            'type' => 'module',
            'name' => 'JA-Mail',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['core' => '>=1.0.0'],
            'settings' => ['custom_flag' => true],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);

        $mail = collect($response->json('data'))->firstWhere('slug', 'mail');
        $this->assertNotNull($mail);
        $this->assertFalse((bool) $mail['is_core']);
        $this->assertEquals('Commercial PRO', $mail['license']);
        $this->assertStringContainsString('webmail', strtolower((string) ($mail['description'] ?? '')));
        $this->assertEquals('mail', $mail['settings']['settings_route'] ?? null);
        $this->assertEquals('pro', $mail['settings']['license_tier'] ?? null);
        $this->assertTrue((bool) ($mail['settings']['custom_flag'] ?? false));
        // dependencies not declared on Mail manifest → preserve prior requirements
        $this->assertEquals(['core' => '>=1.0.0'], $mail['requirements']);
    }

    public function test_library_and_publishing_are_discovered_as_optional_modules(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $data = collect($response->json('data'));

        $library = $data->firstWhere('slug', 'library');
        $publishing = $data->firstWhere('slug', 'publishing');

        $this->assertNotNull($library);
        $this->assertFalse((bool) $library['is_core']);
        $this->assertEquals('module', $library['type']);

        $this->assertNotNull($publishing);
        $this->assertFalse((bool) $publishing['is_core']);
        $this->assertEquals(['library' => '>=1.0.0'], $publishing['requirements']);
    }

    public function test_media_is_discovered_as_optional_module(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $media = collect($response->json('data'))->firstWhere('slug', 'media');

        $this->assertNotNull($media);
        $this->assertFalse((bool) $media['is_core']);
        $this->assertEquals('module', $media['type']);
    }

    public function test_layout_is_discovered_as_optional_module(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $layout = collect($response->json('data'))->firstWhere('slug', 'layout');

        $this->assertNotNull($layout);
        $this->assertFalse((bool) $layout['is_core']);
        $this->assertEquals('module', $layout['type']);
    }

    public function test_forms_is_discovered_as_optional_module(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $forms = collect($response->json('data'))->firstWhere('slug', 'forms');

        $this->assertNotNull($forms);
        $this->assertFalse((bool) $forms['is_core']);
        $this->assertEquals('module', $forms['type']);
    }

    public function test_newsletter_is_discovered_as_optional_module(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $newsletter = collect($response->json('data'))->firstWhere('slug', 'newsletter');

        $this->assertNotNull($newsletter);
        $this->assertFalse((bool) $newsletter['is_core']);
        $this->assertEquals('module', $newsletter['type']);
    }

    public function test_analytics_is_discovered_as_optional_module(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $analytics = collect($response->json('data'))->firstWhere('slug', 'analytics');

        $this->assertNotNull($analytics);
        $this->assertFalse((bool) $analytics['is_core']);
        $this->assertEquals('module', $analytics['type']);
    }

    public function test_search_is_discovered_as_optional_module(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $search = collect($response->json('data'))->firstWhere('slug', 'search');

        $this->assertNotNull($search);
        $this->assertFalse((bool) $search['is_core']);
        $this->assertEquals('module', $search['type']);
    }

    public function test_cms_ai_is_discovered_as_optional_module(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $cmsAi = collect($response->json('data'))->firstWhere('slug', 'cms-ai');

        $this->assertNotNull($cmsAi);
        $this->assertFalse((bool) $cmsAi['is_core']);
        $this->assertEquals('module', $cmsAi['type']);
        $this->assertEquals('cms', $cmsAi['family']);
    }

    public function test_publishing_is_discovered_in_cms_family(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $publishing = collect($response->json('data'))->firstWhere('slug', 'publishing');
        $mail = collect($response->json('data'))->firstWhere('slug', 'mail');

        $this->assertNotNull($publishing);
        $this->assertEquals('cms', $publishing['family']);
        $this->assertNotNull($mail);
        $this->assertEquals('communications', $mail['family']);
    }

    public function test_lifecycle_preview_lists_unsatisfied_requires(): void
    {
        Extension::create([
            'slug' => 'preview-base',
            'type' => 'plugin',
            'name' => 'Preview Base',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $ext = Extension::create([
            'slug' => 'preview-child',
            'type' => 'plugin',
            'name' => 'Preview Child',
            'version' => '1.0.0',
            'database_version' => '0.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['preview-base' => '>=1.0.0'],
            'manifest' => ['suggests' => ['optional-pack' => '*']],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/infra/extensions/{$ext->slug}/lifecycle-preview?intent=activate");

        $response->assertStatus(200);
        $this->assertFalse($response->json('data.can_proceed'));
        $this->assertSame('preview-base', $response->json('data.requires.0.slug'));
        $this->assertFalse($response->json('data.requires.0.satisfied'));
    }

    public function test_deactivate_blocked_when_active_dependent_exists(): void
    {
        $base = Extension::create([
            'slug' => 'graph-base',
            'type' => 'plugin',
            'name' => 'Graph Base',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);

        Extension::create([
            'slug' => 'graph-child',
            'type' => 'plugin',
            'name' => 'Graph Child',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'requirements' => ['graph-base' => '>=1.0.0'],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$base->slug}/deactivate");

        $response->assertStatus(400);
        $this->assertStringContainsString('masih dipakai', $response->json('message'));
        $this->assertEquals('active', $base->fresh()->status);
    }

    public function test_uninstall_blocked_when_active_dependent_exists(): void
    {
        $base = Extension::create([
            'slug' => 'graph-base-uninstall',
            'type' => 'plugin',
            'name' => 'Graph Base Uninstall',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);

        Extension::create([
            'slug' => 'graph-child-uninstall',
            'type' => 'plugin',
            'name' => 'Graph Child Uninstall',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'requirements' => ['graph-base-uninstall' => '>=1.0.0'],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/manage/infra/extensions/{$base->slug}/uninstall");

        $response->assertStatus(400);
        $this->assertStringContainsString('masih dipakai', $response->json('message'));
        $this->assertNotNull($base->fresh());
        $this->assertEquals('active', $base->fresh()->status);
    }

    public function test_lifecycle_preview_cascade_can_proceed_when_required_dep_is_inactive(): void
    {
        Extension::create([
            'slug' => 'cascade-lib',
            'type' => 'plugin',
            'name' => 'Cascade Lib',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'family' => 'cms',
        ]);

        $child = Extension::create([
            'slug' => 'cascade-pub',
            'type' => 'plugin',
            'name' => 'Cascade Pub',
            'version' => '1.0.0',
            'database_version' => '0.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'family' => 'cms',
            'requirements' => ['cascade-lib' => '>=1.0.0'],
        ]);

        $blocked = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/infra/extensions/{$child->slug}/lifecycle-preview?intent=activate");
        $blocked->assertStatus(200);
        $this->assertFalse($blocked->json('data.can_proceed'));

        $cascaded = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/infra/extensions/{$child->slug}/lifecycle-preview?intent=activate&cascade=1");
        $cascaded->assertStatus(200);
        $this->assertTrue($cascaded->json('data.can_proceed'));
        $this->assertSame(
            ['cascade-lib', 'cascade-pub'],
            array_column($cascaded->json('data.cascade_plan.will_activate'), 'slug'),
        );
    }

    public function test_cascade_activate_activates_required_dependencies(): void
    {
        $base = Extension::create([
            'slug' => 'cascade-base',
            'type' => 'plugin',
            'name' => 'Cascade Base',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $child = Extension::create([
            'slug' => 'cascade-child',
            'type' => 'plugin',
            'name' => 'Cascade Child',
            'version' => '1.0.0',
            'database_version' => '0.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['cascade-base' => '>=1.0.0'],
        ]);

        $without = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$child->slug}/activate");
        $without->assertStatus(400);
        $this->assertEquals('inactive', $base->fresh()->status);

        $with = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$child->slug}/activate?cascade=1");
        $with->assertStatus(200);
        $this->assertEquals('active', $base->fresh()->status);
        $this->assertEquals('active', $child->fresh()->status);
    }

    public function test_cascade_activate_fails_on_dependency_cycle(): void
    {
        Extension::create([
            'slug' => 'cycle-a',
            'type' => 'plugin',
            'name' => 'Cycle A',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['cycle-b' => '*'],
        ]);
        Extension::create([
            'slug' => 'cycle-b',
            'type' => 'plugin',
            'name' => 'Cycle B',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['cycle-a' => '*'],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/cycle-a/activate?cascade=1');

        $response->assertStatus(400);
        $this->assertStringContainsString('Siklus', $response->json('message'));
        $this->assertEquals('inactive', Extension::where('slug', 'cycle-a')->value('status'));
        $this->assertEquals('inactive', Extension::where('slug', 'cycle-b')->value('status'));
    }

    public function test_bulk_activate_cms_family_follows_dependency_order(): void
    {
        Extension::create([
            'slug' => 'library',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Library',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);
        Extension::create([
            'slug' => 'publishing',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Publishing',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['library' => '>=1.0.0'],
        ]);
        Extension::create([
            'slug' => 'layout',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Layout',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['publishing' => '>=1.0.0'],
        ]);

        $plan = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions/activation-plan?family=cms');
        $plan->assertStatus(200);
        $this->assertSame(
            ['library', 'publishing', 'layout'],
            array_column($plan->json('data.will_activate'), 'slug'),
        );

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/bulk-activate', ['family' => 'cms']);
        $response->assertStatus(200);
        $this->assertEquals('active', Extension::where('slug', 'library')->value('status'));
        $this->assertEquals('active', Extension::where('slug', 'publishing')->value('status'));
        $this->assertEquals('active', Extension::where('slug', 'layout')->value('status'));
    }

    public function test_bulk_deactivate_cms_family_follows_reverse_dependency_order(): void
    {
        Extension::create([
            'slug' => 'library',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Library',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);
        Extension::create([
            'slug' => 'publishing',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Publishing',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'requirements' => ['library' => '>=1.0.0'],
        ]);
        Extension::create([
            'slug' => 'layout',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Layout',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'requirements' => ['publishing' => '>=1.0.0'],
        ]);
        Extension::create([
            'slug' => 'site',
            'type' => 'module',
            'family' => 'audience',
            'name' => 'Site',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'requirements' => ['layout' => '>=1.0.0'],
        ]);

        $plan = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions/deactivation-plan?family=cms');
        $plan->assertStatus(200);
        $this->assertSame(
            ['site', 'layout', 'publishing', 'library'],
            array_column($plan->json('data.will_deactivate'), 'slug'),
        );

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/bulk-deactivate', ['family' => 'cms']);
        $response->assertStatus(200);
        $this->assertEquals('inactive', Extension::where('slug', 'site')->value('status'));
        $this->assertEquals('inactive', Extension::where('slug', 'layout')->value('status'));
        $this->assertEquals('inactive', Extension::where('slug', 'publishing')->value('status'));
        $this->assertEquals('inactive', Extension::where('slug', 'library')->value('status'));
    }

    public function test_runtime_php_constraint_blocks_activation(): void
    {
        $ext = Extension::create([
            'slug' => 'needs-future-php',
            'type' => 'plugin',
            'name' => 'Future PHP',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'manifest' => ['requires' => ['php' => '>=99.0']],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/activate");

        $response->assertStatus(400);
        $this->assertStringContainsString('php', strtolower((string) $response->json('message')));
        $this->assertEquals('inactive', $ext->fresh()->status);
    }

    public function test_failed_cascade_rolls_back_earlier_activations(): void
    {
        $base = Extension::create([
            'slug' => 'rollback-base',
            'type' => 'plugin',
            'name' => 'Rollback Base',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        Extension::create([
            'slug' => 'rollback-child',
            'type' => 'plugin',
            'name' => 'Rollback Child',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['rollback-base' => '>=1.0.0'],
            'settings' => ['__test_fail_activate' => true],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/rollback-child/activate?cascade=1');

        $response->assertStatus(400);
        $this->assertEquals('inactive', $base->fresh()->status);
        $this->assertEquals('inactive', Extension::where('slug', 'rollback-child')->value('status'));
        $this->assertTrue(
            ExtensionLog::query()
                ->where('extension_slug', 'rollback-base')
                ->where('action', 'activate_rollback')
                ->exists(),
        );
    }

    public function test_activate_writes_audit_log_with_actor(): void
    {
        $ext = Extension::create([
            'slug' => 'audit-plugin',
            'type' => 'plugin',
            'name' => 'Audit Plugin',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/activate")
            ->assertStatus(200);

        $log = ExtensionLog::query()
            ->where('extension_slug', 'audit-plugin')
            ->where('action', 'activate')
            ->where('status', 'success')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->admin->id, $log->performed_by);
        $this->assertNotNull($log->created_at);
    }

    public function test_community_license_blocks_pro_pack_activation(): void
    {
        Setting::set('license_type', 'community', 'string', 'license');

        $ext = Extension::create([
            'slug' => 'pro-only-pack',
            'type' => 'plugin',
            'name' => 'Pro Only Pack',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'settings' => ['license_tier' => 'pro'],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/activate");

        $response->assertStatus(400);
        $this->assertStringContainsString('Lisensi', (string) $response->json('message'));
        $this->assertEquals('inactive', $ext->fresh()->status);
    }

    public function test_index_health_flags_route_conflicts(): void
    {
        Extension::create([
            'slug' => 'pack-alpha',
            'type' => 'plugin',
            'name' => 'Pack Alpha',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);
        Extension::create([
            'slug' => 'pack-beta',
            'type' => 'plugin',
            'name' => 'Pack Beta',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
        ]);

        ConsoleMenu::create([
            'group_slug' => 'editorial',
            'name' => 'Alpha Route',
            'route_name' => 'shared.conflict.route',
            'extension_slug' => 'pack-alpha',
            'order' => 1,
            'is_visible' => true,
        ]);
        ConsoleMenu::create([
            'group_slug' => 'insight',
            'name' => 'Beta Route',
            'route_name' => 'shared.conflict.route',
            'extension_slug' => 'pack-beta',
            'order' => 1,
            'is_visible' => true,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $alpha = collect($response->json('data'))->firstWhere('slug', 'pack-alpha');
        $this->assertNotNull($alpha);
        $this->assertEquals('error', $alpha['health']['status'] ?? null);
        $codes = array_column($alpha['health']['issues'] ?? [], 'code');
        $this->assertContains('route_conflict', $codes);
    }

    public function test_activate_seeds_manifest_permissions(): void
    {
        $ext = Extension::create([
            'slug' => 'caps-pack',
            'type' => 'plugin',
            'name' => 'Caps Pack',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'manifest' => ['permissions' => ['view caps pack', 'manage caps pack']],
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/manage/infra/extensions/{$ext->slug}/activate")
            ->assertStatus(200);

        $this->assertTrue(
            Permission::query()->where('name', 'view caps pack')->where('guard_name', 'web')->exists(),
        );
        $this->assertTrue(
            Permission::query()->where('name', 'manage caps pack')->where('guard_name', 'web')->exists(),
        );
    }

    public function test_health_warns_when_declared_permissions_missing(): void
    {
        Extension::create([
            'slug' => 'ghost-caps',
            'type' => 'plugin',
            'name' => 'Ghost Caps',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'active',
            'is_core' => false,
            'manifest' => ['permissions' => ['view ghost caps']],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/extensions');

        $response->assertStatus(200);
        $row = collect($response->json('data'))->firstWhere('slug', 'ghost-caps');
        $this->assertNotNull($row);
        $codes = array_column($row['health']['issues'] ?? [], 'code');
        $this->assertContains('missing_permissions', $codes);
        $this->assertEquals('warning', $row['health']['status']);
    }

    public function test_is_product_active_follows_registry_status(): void
    {
        Extension::flushProductActiveMemo();
        $this->assertFalse(Extension::isProductActive('gated-pack'));

        Extension::create([
            'slug' => 'gated-pack',
            'type' => 'plugin',
            'name' => 'Gated Pack',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);
        Extension::flushProductActiveMemo();
        $this->assertFalse(Extension::isProductActive('gated-pack'));

        Extension::query()->where('slug', 'gated-pack')->update(['status' => 'active']);
        Extension::flushProductActiveMemo();
        $this->assertTrue(Extension::isProductActive('gated-pack'));
    }

    public function test_lifecycle_cache_forget_clears_sidebar_navigation(): void
    {
        Cache::put(ExtensionGraphService::NAV_CACHE_KEY, ['stale' => true], 300);
        app(ExtensionGraphService::class)->forgetLifecycleCaches();
        $this->assertNull(Cache::get(ExtensionGraphService::NAV_CACHE_KEY));
    }

    public function test_activating_publishing_reveals_editorial_console_menus(): void
    {
        Extension::create([
            'slug' => 'library',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Library',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
        ]);
        Extension::create([
            'slug' => 'publishing',
            'type' => 'module',
            'family' => 'cms',
            'name' => 'Publishing',
            'version' => '1.0.0',
            'database_version' => '1.0.0',
            'status' => 'inactive',
            'is_core' => false,
            'requirements' => ['library' => '>=1.0.0'],
        ]);

        ConsoleMenu::ensureMissingDefaults();
        ConsoleMenu::syncVisibilityForExtension('library', false);
        ConsoleMenu::syncVisibilityForExtension('publishing', false);

        $this->assertFalse((bool) ConsoleMenu::query()->where('route_name', 'contents.index')->value('is_visible'));

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/publishing/activate?cascade=1')
            ->assertStatus(200);

        $this->assertEquals('active', Extension::where('slug', 'library')->value('status'));
        $this->assertEquals('active', Extension::where('slug', 'publishing')->value('status'));
        $this->assertTrue((bool) ConsoleMenu::query()->where('route_name', 'contents.index')->value('is_visible'));
        $this->assertTrue((bool) ConsoleMenu::query()->where('route_name', 'tags')->value('is_visible'));

        $menus = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/console-menus')
            ->assertOk()
            ->json('data');

        $visibleContent = collect($menus)
            ->flatMap(fn ($root) => $root['children'] ?? [])
            ->firstWhere('route_name', 'contents.index');

        $this->assertNotNull($visibleContent);
        $this->assertTrue((bool) $visibleContent['is_visible']);
        $this->assertEquals('publishing', $visibleContent['extension_slug']);
    }
}
