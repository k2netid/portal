<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Modules\Member\Models\Member;
use Modules\Member\Models\MemberBookmark;
use Modules\Publishing\Models\Comment;
use Modules\Publishing\Models\Content;
use Tests\TestCase;

class MemberAuthTest extends TestCase
{
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
            ->assertJsonPath('data.email', 'reader@example.com');

        $this->assertDatabaseHas('mem_members', [
            'email' => 'reader@example.com',
        ]);
    }

    public function test_member_bookmark_does_not_use_console_users(): void
    {
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
}
