<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Language;
use Tests\TestCase;

class LocalizationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_public_can_get_active_languages(): void
    {
        Language::create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/v1/public/system/languages');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'code',
                        'name',
                        'is_default',
                    ],
                ],
            ]);
    }

    public function test_admin_can_manage_languages(): void
    {
        $admin = $this->createAdminUser();

        $en = Language::create([
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Create
        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/languages', [
                'code' => 'jv',
                'name' => 'Javanese',
                'native_name' => 'Basa Jawa',
                'flag' => '🇮🇩',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 5,
            ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.code', 'jv')
            ->assertJsonPath('data.name', 'Javanese');

        $languageId = (string) $createResponse->json('data.id');

        // Update
        $updateResponse = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/v1/manage/system/languages/'.$languageId, [
                'name' => 'Javanese Language',
                'native_name' => 'Basa Jawa Anyar',
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.name', 'Javanese Language');

        // Set Default
        $setDefaultResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/languages/'.$languageId.'/set-default');

        $setDefaultResponse->assertOk();
        $this->assertTrue((bool) Language::findOrFail($languageId)->is_default);

        // Reset default back to English so we can delete Javanese
        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/languages/'.$en->id.'/set-default')
            ->assertOk();

        // Delete
        $deleteResponse = $this->actingAs($admin, 'sanctum')
            ->deleteJson('/api/v1/manage/system/languages/'.$languageId);

        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('sys_languages', ['id' => $languageId]);
    }

    public function test_admin_can_get_and_set_translations(): void
    {
        $admin = $this->createAdminUser();

        Language::create([
            'code' => 'id',
            'name' => 'Indonesian',
            'native_name' => 'Bahasa Indonesia',
            'is_default' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $setResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/manage/system/translations', [
                'translatable_type' => 'App\\Models\\Post',
                'translatable_id' => 'post-uuid-1234',
                'language_code' => 'id',
                'field' => 'title',
                'value' => 'Judul Artikel Bahasa Indonesia',
            ]);

        $setResponse->assertOk()
            ->assertJsonPath('data.field', 'title')
            ->assertJsonPath('data.value', 'Judul Artikel Bahasa Indonesia');

        $getResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/translations?translatable_type=App%5CModels%5CPost&translatable_id=post-uuid-1234');

        $getResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'translatable_type',
                        'translatable_id',
                        'language_code',
                        'field',
                        'value',
                    ],
                ],
            ]);
    }

    public function test_unauthenticated_cannot_manage_languages_or_translations(): void
    {
        $this->getJson('/api/v1/manage/system/languages')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/languages', [])->assertUnauthorized();
        $this->getJson('/api/v1/manage/system/translations?translatable_type=Post&translatable_id=1')->assertUnauthorized();
        $this->postJson('/api/v1/manage/system/translations', [])->assertUnauthorized();
    }
}
