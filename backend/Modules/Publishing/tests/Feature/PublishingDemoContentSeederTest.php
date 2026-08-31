<?php

declare(strict_types=1);

namespace Modules\Publishing\Tests\Feature;

use Modules\Core\System\Models\User;
use Modules\Publishing\Database\Seeders\PublishingDemoContentSeeder;
use Modules\Publishing\Models\Content;
use Tests\TestCase;

class PublishingDemoContentSeederTest extends TestCase
{
    public function test_skips_when_demo_flag_off(): void
    {
        config(['install.seed_demo' => false]);
        $this->seedPermissionsAndRoles();
        $admin = $this->createAdminUser();

        PublishingDemoContentSeeder::ensure();

        $this->assertSame(0, Content::query()->count());
        unset($admin);
    }

    public function test_creates_welcome_post_when_demo_flag_on_and_blog_empty(): void
    {
        config(['install.seed_demo' => true]);
        $this->seedPermissionsAndRoles();
        $super = $this->createSuperAdminUser();

        PublishingDemoContentSeeder::ensure();

        $this->assertSame(1, Content::query()->where('slug', 'welcome')->count());
        $post = Content::query()->where('slug', 'welcome')->first();
        $this->assertNotNull($post);
        $this->assertSame('published', $post->status);
        $this->assertSame($super->id, $post->author_id);
    }
}
