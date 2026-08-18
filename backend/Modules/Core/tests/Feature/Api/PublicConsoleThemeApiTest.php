<?php

namespace Modules\Core\Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Services\ConsoleThemeService;
use Tests\TestCase;

class PublicConsoleThemeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_console_theme_returns_resolved_settings_without_auth(): void
    {
        $response = $this->getJson('/api/v1/public/system/console-theme');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'settings',
                ],
            ]);

        $expected = app(ConsoleThemeService::class)->getResolvedSettings();
        $this->assertSame($expected, $response->json('data.settings'));
    }
}
