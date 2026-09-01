<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Illuminate\Support\Facades\URL;
use Modules\Core\System\Contracts\OutboundMailPortInterface;
use Modules\Core\System\Models\Extension;
use Modules\Member\Models\Member;
use Modules\Member\Models\MemberBookmark;
use Modules\Member\Services\MemberEmailVerification;
use Modules\Member\Tests\Concerns\SoftensPasswordPolicyForTests;
use Modules\Publishing\Models\Comment;
use Modules\Publishing\Models\Content;
use Tests\TestCase;

class MemberAuthTest extends TestCase
{
    use SoftensPasswordPolicyForTests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->softenPasswordPolicyForTests();
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

    private function verifyMember(string $id): void
    {
        Member::query()->whereKey($id)->update(['email_verified_at' => now()]);
    }

    public function test_member_can_login_with_token_not_console_session(): void
    {
        $this->registerMember();

        $response = $this->postJson('/api/v1/public/member/login', [
            'email' => 'reader@example.com',
            'password' => 'password12',
        ]);

        $response->assertOk();
        $this->assertIsString($response->json('data.token'));
        $this->assertSame('reader@example.com', $response->json('data.member.email'));
        $this->assertDatabaseMissing('srv_auth_users', ['email' => 'reader@example.com']);
    }

    public function test_member_can_register_and_fetch_profile(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->getJson('/api/v1/member/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'reader@example.com')
            ->assertJsonPath('data.email_verified', false);

        $this->assertDatabaseHas('mem_members', [
            'email' => 'reader@example.com',
        ]);
    }

    public function test_member_registration_respects_enable_member_registration_setting(): void
    {
        \Modules\Core\System\Models\Setting::set('enable_member_registration', false, 'boolean', 'security');

        $this->postJson('/api/v1/public/member/register', [
            'name' => 'Blocked Reader',
            'email' => 'blocked-reader@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ])
            ->assertForbidden()
            ->assertJsonPath('error_code', 'MEMBER_REGISTRATION_DISABLED');

        $this->assertDatabaseMissing('mem_members', [
            'email' => 'blocked-reader@example.com',
        ]);

        // Console operator flag must not gate reader signup.
        \Modules\Core\System\Models\Setting::set('enable_registration', false, 'boolean', 'security');
        \Modules\Core\System\Models\Setting::set('enable_member_registration', true, 'boolean', 'security');

        $this->postJson('/api/v1/public/member/register', [
            'name' => 'Allowed Reader',
            'email' => 'allowed-reader@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ])->assertCreated();
    }

    public function test_member_bookmark_does_not_use_console_users(): void
    {
        $this->activatePack('publishing');
        $this->seedPermissionsAndRoles();
        $admin = $this->createAdminUser();
        $content = Content::factory()->published()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'comment_status' => 'open',
        ]);

        $auth = $this->registerMember();
        $this->verifyMember($auth['id']);

        $this->withToken($auth['token'])
            ->postJson('/api/v1/member/bookmarks', [
                'content_id' => $content->id,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('mem_bookmarks', [
            'member_id' => $auth['id'],
            'content_id' => $content->id,
        ]);
        $this->assertDatabaseCount('pub_bookmarks', 0);
        $this->assertTrue(MemberBookmark::query()->where('member_id', $auth['id'])->exists());
    }

    public function test_member_comment_sets_member_id_not_console_user_id(): void
    {
        $this->seedPermissionsAndRoles();
        $admin = $this->createAdminUser();
        $content = Content::factory()->published()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'comment_status' => 'open',
        ]);

        $auth = $this->registerMember();
        $this->verifyMember($auth['id']);

        $this->withToken($auth['token'])
            ->postJson("/api/v1/public/publishing/contents/{$content->id}/comments", [
                'body' => 'Hello from a reader, not a console user.',
            ])
            ->assertCreated();

        $comment = Comment::query()->latest()->first();
        $this->assertNotNull($comment);
        $this->assertEquals($auth['id'], $comment->member_id);
        $this->assertNull($comment->user_id);
        $this->assertDatabaseMissing('srv_auth_users', ['id' => $auth['id']]);
        $this->assertTrue(Member::query()->whereKey($auth['id'])->exists());
    }

    public function test_unverified_member_cannot_bookmark_or_comment(): void
    {
        $this->activatePack('publishing');
        $this->seedPermissionsAndRoles();
        $admin = $this->createAdminUser();
        $content = Content::factory()->published()->create([
            'author_id' => $admin->id,
            'category_id' => null,
            'comment_status' => 'open',
        ]);

        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->postJson('/api/v1/member/bookmarks', [
                'content_id' => $content->id,
            ])
            ->assertForbidden()
            ->assertJsonPath('error_code', 'EMAIL_UNVERIFIED');

        $this->withToken($auth['token'])
            ->postJson("/api/v1/public/publishing/contents/{$content->id}/comments", [
                'body' => 'Unverified reader should not comment.',
            ])
            ->assertForbidden()
            ->assertJsonPath('error_code', 'EMAIL_UNVERIFIED');
    }

    public function test_register_sends_verify_mail_and_signed_link_confirms(): void
    {
        $this->activatePack('mail');
        $html = null;
        $this->mock(OutboundMailPortInterface::class, function ($mock) use (&$html): void {
            $mock->shouldReceive('send')
                ->once()
                ->andReturnUsing(function (...$args) use (&$html): array {
                    $html = is_string($args[2] ?? null) ? $args[2] : '';

                    return ['status' => 'sent'];
                });
        });

        $auth = $this->registerMember();
        $this->assertIsString($html);
        $this->assertStringContainsString('reader@example.com', (string) $html);
        $this->assertMatchesRegularExpression('/href="([^"]+)"/', (string) $html);
        preg_match('/href="([^"]+)"/', (string) $html, $matches);
        $url = html_entity_decode($matches[1], ENT_QUOTES);

        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('data.email_verified', true);

        $this->assertNotNull(
            Member::query()->whereKey($auth['id'])->value('email_verified_at'),
        );

        $this->withToken($auth['token'])
            ->getJson('/api/v1/member/me')
            ->assertOk()
            ->assertJsonPath('data.email_verified', true);
    }

    public function test_verify_email_rejects_unsigned_and_wrong_hash(): void
    {
        $auth = $this->registerMember();
        $member = Member::query()->findOrFail($auth['id']);
        $hash = sha1((string) $member->email);

        $this->getJson("/api/v1/public/member/verify-email/{$member->id}/{$hash}")
            ->assertForbidden();

        $bad = URL::temporarySignedRoute(
            'member.verify-email',
            now()->addHour(),
            ['id' => $member->id, 'hash' => sha1('other@example.com')],
        );

        $this->getJson($bad)->assertForbidden();
        $this->assertNull($member->fresh()?->email_verified_at);
    }

    public function test_verify_email_browser_hit_redirects_to_public_site(): void
    {
        config(['app.frontend_url' => 'http://localhost:5273']);

        $auth = $this->registerMember();
        $member = Member::query()->findOrFail($auth['id']);
        $url = app(MemberEmailVerification::class)->signedUrl($member);

        $this->get($url, ['Accept' => 'text/html'])->assertRedirect('http://localhost:5273/member/verified?status=ok');
        $this->assertNotNull($member->fresh()?->email_verified_at);
    }

    public function test_member_can_resend_verification_email(): void
    {
        $this->activatePack('mail');
        $this->mock(OutboundMailPortInterface::class, function ($mock): void {
            $mock->shouldReceive('send')->twice()->andReturn(['status' => 'sent']);
        });

        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->postJson('/api/v1/member/email/verification-notification')
            ->assertOk();
    }

    public function test_member_api_forbidden_when_pack_inactive(): void
    {
        $memberPack = Extension::query()->where('slug', 'member')->first();
        $memberPack?->update(['status' => 'inactive']);

        $this->postJson('/api/v1/public/member/register', [
            'name' => 'Reader Two',
            'email' => 'reader2@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ])->assertForbidden();
    }
}
