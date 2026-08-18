<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Modules\Core\System\Models\Extension;
use Modules\Core\System\Models\User;
use Modules\Core\System\Support\ExtensionPaths;
use Tests\TestCase;
use ZipArchive;

class ExtensionSecuritySandboxTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected string $tempZipPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();

        Extension::truncate();
    }

    protected function tearDown(): void
    {
        if (isset($this->tempZipPath) && file_exists($this->tempZipPath)) {
            @unlink($this->tempZipPath);
        }

        $testPluginDir = ExtensionPaths::pluginDirectory('bypass-plugin');
        if (File::exists($testPluginDir)) {
            File::deleteDirectory($testPluginDir);
        }

        parent::tearDown();
    }

    /**
     * Test AST scanner blocks dynamic execution string-concatenation bypasses (e.g. $func = 'sys' . 'tem'; $func('rm -rf');).
     */
    public function test_ast_scanner_blocks_obfuscated_dynamic_calls(): void
    {
        $this->tempZipPath = tempnam(sys_get_temp_dir(), 'zip').'.zip';

        $zip = new ZipArchive;
        if ($zip->open($this->tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->fail('Failed to create test ZIP file.');
        }

        $manifest = [
            'slug' => 'bypass-plugin',
            'name' => 'Bypass Attempt Plugin',
            'type' => 'plugin',
            'version' => '1.0.0',
        ];

        $zip->addFromString('manifest.json', json_encode($manifest));

        // Malicious file using dynamic concatenation string execution to bypass simple regex checks
        $maliciousCode = <<<'PHP'
<?php
namespace Extensions\BypassPlugin;

class Exploit {
    public function run() {
        $a = 'sh' . 'ell_ex' . 'ec';
        $a("echo vulnerable");
    }
}
PHP;

        $zip->addFromString('src/Exploit.php', $maliciousCode);
        $zip->close();

        $uploadedFile = new UploadedFile(
            $this->tempZipPath,
            'bypass-plugin.zip',
            'application/zip',
            null,
            true
        );

        // Upload through controller endpoint
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/upload', [
                'file' => $uploadedFile,
            ]);

        // Assert 400 Bad Request blocked by Security Gate!
        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Security Gate Violation', $response->json('message'));
        $this->assertStringContainsString('Eksekusi fungsi dinamis terdeteksi', $response->json('message'));
    }

    /**
     * Test AST scanner blocks shell backtick executions (e.g. `ls -la`).
     */
    public function test_ast_scanner_blocks_shell_backticks(): void
    {
        $this->tempZipPath = tempnam(sys_get_temp_dir(), 'zip').'.zip';

        $zip = new ZipArchive;
        if ($zip->open($this->tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->fail('Failed to create test ZIP file.');
        }

        $manifest = [
            'slug' => 'bypass-plugin',
            'name' => 'Bypass Attempt Plugin',
            'type' => 'plugin',
            'version' => '1.0.0',
        ];

        $zip->addFromString('manifest.json', json_encode($manifest));

        $maliciousCode = <<<'PHP'
<?php
namespace Extensions\BypassPlugin;

class BacktickExploit {
    public function execute() {
        $output = `whoami`;
        return $output;
    }
}
PHP;

        $zip->addFromString('src/BacktickExploit.php', $maliciousCode);
        $zip->close();

        $uploadedFile = new UploadedFile(
            $this->tempZipPath,
            'bypass-plugin.zip',
            'application/zip',
            null,
            true
        );

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/extensions/upload', [
                'file' => $uploadedFile,
            ]);

        $response->assertStatus(400);
        $this->assertStringContainsString('Security Gate Violation', $response->json('message'));
        $this->assertStringContainsString('operator backtick shell terdeteksi', $response->json('message'));
    }
}
