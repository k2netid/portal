<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Feature;

use Modules\Core\System\Models\User;
use Tests\TestCase;

class PluginThemeSlotsApiTest extends TestCase
{
    public function test_manage_plugin_theme_slots_lists_known_slots(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/manage/layout/plugin-theme-slots');

        $response->assertOk();
        $response->assertJsonPath('data.slots.0.id', 'after_header');
        $response->assertJsonFragment(['after_post_content']);
    }
}
