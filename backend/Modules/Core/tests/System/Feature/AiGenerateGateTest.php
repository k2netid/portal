<?php

declare(strict_types=1);

namespace Modules\Core\Tests\System\Feature;

use Modules\Core\System\Models\Setting;
use Tests\TestCase;

class AiGenerateGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_generate_refuses_when_global_ai_disabled(): void
    {
        Setting::set('ai_enabled', false, 'boolean', 'ai');
        Setting::set('gemini_api_key', 'test-key', 'password', 'ai');

        $user = $this->createSuperAdminUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/ai/generate', [
                'prompt' => 'Say hello',
            ])
            ->assertForbidden()
            ->assertJsonPath('error_code', 'AI_DISABLED');
    }
}
