<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SystemJournalApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $testLogFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();

        $logPath = storage_path('logs');
        if (! File::isDirectory($logPath)) {
            File::makeDirectory($logPath, 0755, true);
        }

        $this->testLogFile = $logPath.'/laravel-test.log';
        File::put($this->testLogFile, "[2026-08-21 12:00:00] local.INFO: Test system journal info entry\n[2026-08-21 12:01:00] local.ERROR: Test system journal error entry\n");
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testLogFile)) {
            File::delete($this->testLogFile);
        }
        parent::tearDown();
    }

    public function test_admin_can_list_system_journal_files(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system-journal');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'name',
                        'size',
                        'modified',
                    ],
                ],
            ]);
    }

    public function test_admin_can_read_and_filter_log_file(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system-journal/laravel-test.log');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_admin_can_download_log_file(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->get('/api/v1/manage/system-journal/laravel-test.log/download');

        $response->assertOk();
    }

    public function test_public_can_post_frontend_telemetry_logs(): void
    {
        $response = $this->postJson('/api/v1/journal/frontend', [
            'level' => 'error',
            'message' => 'Uncaught TypeError in frontend bundle',
            'context' => [
                'route' => '/dashboard',
                'browser' => 'Chrome 120.0',
            ],
        ]);

        $response->assertOk();
    }

    public function test_unauthenticated_cannot_read_system_journals(): void
    {
        $this->getJson('/api/v1/manage/system-journal')->assertUnauthorized();
        $this->getJson('/api/v1/manage/system-journal/laravel-test.log')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system-journal/clear', [])->assertUnauthorized();
    }
}
