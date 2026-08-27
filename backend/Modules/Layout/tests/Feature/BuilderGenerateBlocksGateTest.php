<?php

declare(strict_types=1);

namespace Modules\Layout\Tests\Feature;

use Tests\TestCase;

class BuilderGenerateBlocksGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->activatePack('layout');
    }

    public function test_generate_blocks_forbidden_when_ai_disabled(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/layout/builder/generate-blocks', [
                'prompt' => 'Hero for a bakery',
            ])
            ->assertForbidden()
            ->assertJsonPath('error_code', 'AI_DISABLED');
    }
}
