<?php

declare(strict_types=1);

namespace Modules\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\Core\System\Models\User;
use Modules\Publishing\Models\Content;

/**
 * Optional welcome post when INSTALL_SEED_DEMO=true and no posts exist yet.
 */
class PublishingDemoContentSeeder extends Seeder
{
    public static function ensure(): void
    {
        (new self)->run();
    }

    public function run(): void
    {
        if (! (bool) config('install.seed_demo', false)) {
            return;
        }

        if (! Schema::hasTable('pub_contents')) {
            return;
        }

        if (Content::query()->where('slug', 'welcome')->exists()) {
            return;
        }

        if (Content::query()->where('type', 'post')->exists()) {
            return;
        }

        $author = User::query()
            ->whereHas('roles', static fn ($query) => $query->where('name', 'super'))
            ->first();

        if ($author === null) {
            $author = User::query()->first();
        }

        if ($author === null) {
            return;
        }

        Content::query()->create([
            'title' => 'Welcome',
            'slug' => 'welcome',
            'excerpt' => 'Sample post seeded for demo installs. Remove or replace after go-live.',
            'body' => '<p>This article was created because <code>INSTALL_SEED_DEMO=true</code>. It helps verify the public blog and member bookmarks without manual CMS setup.</p>',
            'status' => 'published',
            'type' => 'post',
            'author_id' => $author->id,
            'category_id' => null,
            'published_at' => now(),
            'comment_status' => 'open',
        ]);
    }
}
