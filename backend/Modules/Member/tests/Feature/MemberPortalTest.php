<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Modules\Core\System\Models\Extension;
use Modules\Member\Models\Member;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->activatePack('member');
    }

    /**
     * @return array{token: string, id: string}
     */
    private function registerMember(): array
    {
        $response = $this->postJson('/api/v1/public/member/register', [
            'name' => 'Reader One',
            'email' => 'reader@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ]);

        $response->assertCreated();
        $token = $response->json('data.token');
        $id = $response->json('data.member.id');
        $this->assertIsString($token);
        $this->assertIsString($id);

        return ['token' => $token, 'id' => $id];
    }

    public function test_member_portal_returns_core_navigation(): void
    {
        $auth = $this->registerMember();

        $response = $this->withToken($auth['token'])
            ->getJson('/api/v1/member/portal')
            ->assertOk()
            ->assertJsonPath('data.member.email', 'reader@example.com')
            ->assertJsonPath('data.capabilities.0', 'member.portal');

        $routes = collect($response->json('data.navigation'))->pluck('route')->all();
        $this->assertContains('member.dashboard', $routes);
        $this->assertContains('member.profile', $routes);
        $this->assertContains('member.security', $routes);
    }

    public function test_member_portal_includes_publishing_capabilities_when_active(): void
    {
        $this->activatePack('publishing', [
            'manifest' => json_decode(
                (string) file_get_contents(base_path('Modules/Publishing/manifest.json')),
                true,
            ),
        ]);

        $auth = $this->registerMember();
        Member::query()->whereKey($auth['id'])->update(['email_verified_at' => now()]);

        $response = $this->withToken($auth['token'])
            ->getJson('/api/v1/member/portal')
            ->assertOk();

        $capabilities = $response->json('data.capabilities');
        $this->assertIsArray($capabilities);
        $this->assertContains('member.bookmarks', $capabilities);
        $this->assertContains('member.comments', $capabilities);

        $routes = collect($response->json('data.navigation'))->pluck('route')->all();
        $this->assertContains('member.bookmarks', $routes);
        $this->assertContains('member.comments', $routes);
    }

    public function test_member_portal_omits_publishing_when_pack_inactive(): void
    {
        $auth = $this->registerMember();
        Member::query()->whereKey($auth['id'])->update(['email_verified_at' => now()]);

        $response = $this->withToken($auth['token'])
            ->getJson('/api/v1/member/portal')
            ->assertOk();

        $capabilities = $response->json('data.capabilities');
        $this->assertIsArray($capabilities);
        $this->assertNotContains('member.bookmarks', $capabilities);
    }

    public function test_member_can_update_profile_name(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->patchJson('/api/v1/member/profile', ['name' => 'Reader Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Reader Updated');

        $this->assertDatabaseHas('mem_members', [
            'id' => $auth['id'],
            'name' => 'Reader Updated',
        ]);
    }

    public function test_member_can_update_password(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->putJson('/api/v1/member/password', [
                'current_password' => 'password12',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertOk();

        $this->postJson('/api/v1/public/member/login', [
            'email' => 'reader@example.com',
            'password' => 'newpass123',
        ])->assertOk();
    }

    public function test_member_bookmarks_forbidden_when_publishing_inactive(): void
    {
        $auth = $this->registerMember();
        Member::query()->whereKey($auth['id'])->update(['email_verified_at' => now()]);

        Extension::query()->where('slug', 'publishing')->update(['status' => 'inactive']);
        Extension::flushProductActiveMemo();

        $this->withToken($auth['token'])
            ->getJson('/api/v1/member/bookmarks')
            ->assertForbidden();
    }

    public function test_member_comments_list_when_publishing_active(): void
    {
        $this->activatePack('publishing');
        $this->seedPermissionsAndRoles();
        $admin = $this->createAdminUser();
        $content = \Modules\Publishing\Models\Content::factory()->published()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'comment_status' => 'open',
        ]);

        $auth = $this->registerMember();
        Member::query()->whereKey($auth['id'])->update(['email_verified_at' => now()]);

        $this->withToken($auth['token'])
            ->postJson("/api/v1/public/publishing/contents/{$content->id}/comments", [
                'body' => 'My reader comment',
            ])
            ->assertCreated();

        $this->withToken($auth['token'])
            ->getJson('/api/v1/member/comments')
            ->assertOk()
            ->assertJsonPath('data.data.0.body', 'My reader comment');
    }

    public function test_member_password_update_rejects_wrong_current_password(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->putJson('/api/v1/member/password', [
                'current_password' => 'wrong-password',
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }
}
