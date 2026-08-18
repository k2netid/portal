<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Publishing\Models\Content;
use Tests\TestCase;

class PublishScheduledContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_content_is_published_by_command(): void
    {
        $content = Content::factory()->create([
            'status' => 'scheduled',
            'published_at' => Carbon::now()->subMinute(),
        ]);

        $this->artisan('content:publish-scheduled')->assertSuccessful();

        $content->refresh();
        $this->assertSame('published', $content->status);
    }
}
