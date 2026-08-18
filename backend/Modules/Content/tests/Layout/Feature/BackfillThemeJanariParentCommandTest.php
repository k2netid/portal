<?php

namespace Modules\Content\Layout\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Layout\Models\Theme;
use Tests\TestCase;

class BackfillThemeJanariParentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_update_parent_theme(): void
    {
        Theme::factory()->create([
            'slug' => 'no-folder-theme-xyz',
            'type' => 'frontend',
            'parent_theme' => null,
            'status' => 'active',
        ]);

        $this->artisan('layout:themes:backfill-janari-parent')
            ->assertSuccessful();

        $this->assertNull(Theme::where('slug', 'no-folder-theme-xyz')->value('parent_theme'));
    }

    public function test_apply_sets_parent_when_theme_folder_missing(): void
    {
        Theme::factory()->create([
            'slug' => 'no-folder-theme-abc',
            'type' => 'frontend',
            'parent_theme' => null,
            'status' => 'active',
        ]);

        $this->artisan('layout:themes:backfill-janari-parent', [
            '--apply' => true,
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->assertSame('janari', Theme::where('slug', 'no-folder-theme-abc')->value('parent_theme'));
    }

    public function test_fails_when_parent_slug_has_no_theme_on_disk(): void
    {
        $this->artisan('layout:themes:backfill-janari-parent', [
            '--parent' => '___not_a_theme_folder___',
        ])->assertFailed();
    }
}
