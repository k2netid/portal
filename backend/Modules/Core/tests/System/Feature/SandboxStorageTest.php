<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Support\Facades\File;
use League\Flysystem\PathTraversalDetected;
use Modules\Core\System\Facades\SandboxStorage;
use Tests\TestCase;

class SandboxStorageTest extends TestCase
{
    protected string $testSlug = 'sandbox-test-plugin';

    protected function tearDown(): void
    {
        // Cleanup sandbox test directories
        $sandboxPath = storage_path("app/extensions/{$this->testSlug}");
        if (File::isDirectory($sandboxPath)) {
            File::deleteDirectory($sandboxPath);
        }

        parent::tearDown();
    }

    /**
     * Test that SandboxStorage builds an isolated local storage disk.
     */
    public function test_sandbox_storage_jails_operations_inside_sandbox_directory(): void
    {
        $disk = SandboxStorage::for($this->testSlug);

        // 1. Write a file using the jailed filesystem disk
        $disk->put('logs/app.log', 'Log output content');

        // 2. Verify it exists physically under the isolated path
        $physicalPath = storage_path("app/extensions/{$this->testSlug}/sandbox/logs/app.log");
        $this->assertTrue(file_exists($physicalPath));
        $this->assertEquals('Log output content', file_get_contents($physicalPath));

        // 3. Verify it is accessible via the sandbox disk API
        $this->assertTrue($disk->exists('logs/app.log'));
        $this->assertEquals('Log output content', $disk->get('logs/app.log'));

        // 4. Try directory traversal and verify it is actively blocked by Flysystem
        $this->expectException(PathTraversalDetected::class);
        $disk->put('../traversal.txt', 'Illegal traversal write attempt');
    }
}
