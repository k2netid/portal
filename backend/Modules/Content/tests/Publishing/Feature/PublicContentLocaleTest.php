<?php

namespace Modules\Content\Publishing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\Language;
use Modules\Core\System\Models\Translation;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class PublicContentLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_applies_sys_translation_for_locale_query(): void
    {
        Language::factory()->create(['code' => 'en', 'is_active' => true]);
        Language::factory()->create(['code' => 'id', 'is_active' => true]);

        $user = User::factory()->create();

        $content = Content::factory()->create([
            'slug' => 'home',
            'type' => 'page',
            'status' => 'published',
            'author_id' => $user->id,
            'title' => 'Default Title',
            'body' => '<p>Default body</p>',
            'intro' => 'Default intro',
            'published_at' => now(),
        ]);

        Translation::create([
            'translatable_type' => Content::class,
            'translatable_id' => $content->id,
            'language_code' => 'id',
            'field' => 'title',
            'value' => 'Judul Indonesia',
        ]);

        Translation::create([
            'translatable_type' => Content::class,
            'translatable_id' => $content->id,
            'language_code' => 'id',
            'field' => 'body',
            'value' => '<p>Konten Indonesia</p>',
        ]);

        $response = $this->getJson('/api/v1/public/publishing/contents/home?locale=id');

        $response->assertOk()
            ->assertJsonPath('data.title', 'Judul Indonesia')
            ->assertJsonPath('data.body', '<p>Konten Indonesia</p>');
    }
}
