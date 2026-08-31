<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Modules\Member\Models\Member;
use Tests\TestCase;

class MemberProfileFieldsTest extends TestCase
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
            'email' => 'reader-fields@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ]);

        $response->assertCreated();

        return [
            'token' => (string) $response->json('data.token'),
            'id' => (string) $response->json('data.member.id'),
        ];
    }

    public function test_member_profile_includes_standard_fields(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->getJson('/api/v1/member/me')
            ->assertOk()
            ->assertJsonPath('data.phone', null)
            ->assertJsonPath('data.locale', null)
            ->assertJsonPath('data.timezone', null)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'avatar',
                    'bio',
                    'locale',
                    'timezone',
                    'status',
                    'email_verified',
                    'pending_email',
                    'last_login_at',
                    'created_at',
                ],
            ]);
    }

    public function test_member_can_update_profile_fields(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->patchJson('/api/v1/member/profile', [
                'name' => 'Reader Updated',
                'phone' => '+62 812-3456-7890',
                'bio' => 'Avid reader and commenter.',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'avatar' => 'https://example.com/avatar.png',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Reader Updated')
            ->assertJsonPath('data.phone', '+62 812-3456-7890')
            ->assertJsonPath('data.bio', 'Avid reader and commenter.')
            ->assertJsonPath('data.locale', 'id')
            ->assertJsonPath('data.timezone', 'Asia/Jakarta')
            ->assertJsonPath('data.avatar', 'https://example.com/avatar.png');

        $this->assertDatabaseHas('mem_members', [
            'id' => $auth['id'],
            'name' => 'Reader Updated',
            'phone' => '+62 812-3456-7890',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);
    }

    public function test_member_can_upload_avatar_image(): void
    {
        $auth = $this->registerMember();
        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg', 120, 120);

        $response = $this->withToken($auth['token'])
            ->post('/api/v1/member/profile/avatar', [
                'file' => $file,
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertOk();
        $avatar = $response->json('data.avatar');
        $this->assertIsString($avatar);
        $this->assertNotSame('', $avatar);
        $this->assertStringContainsString('/storage/', $avatar);

        $this->assertDatabaseHas('mem_members', [
            'id' => $auth['id'],
            'avatar' => $avatar,
        ]);

        $relative = ltrim(substr($avatar, strlen('/storage/')), '/');
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($relative));
    }

    public function test_replacing_avatar_deletes_previous_owned_file(): void
    {
        $auth = $this->registerMember();

        $first = $this->withToken($auth['token'])
            ->post('/api/v1/member/profile/avatar', [
                'file' => \Illuminate\Http\UploadedFile::fake()->image('one.jpg', 80, 80),
            ], ['Accept' => 'application/json']);
        $first->assertOk();
        $firstUrl = (string) $first->json('data.avatar');
        $firstPath = ltrim(substr($firstUrl, strlen('/storage/')), '/');
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($firstPath));

        $second = $this->withToken($auth['token'])
            ->post('/api/v1/member/profile/avatar', [
                'file' => \Illuminate\Http\UploadedFile::fake()->image('two.png', 80, 80),
            ], ['Accept' => 'application/json']);
        $second->assertOk();
        $secondUrl = (string) $second->json('data.avatar');
        $this->assertNotSame($firstUrl, $secondUrl);
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists($firstPath));
    }

    public function test_clearing_avatar_deletes_owned_file_but_keeps_external_urls(): void
    {
        $auth = $this->registerMember();

        $upload = $this->withToken($auth['token'])
            ->post('/api/v1/member/profile/avatar', [
                'file' => \Illuminate\Http\UploadedFile::fake()->image('clear-me.jpg', 64, 64),
            ], ['Accept' => 'application/json']);
        $upload->assertOk();
        $ownedUrl = (string) $upload->json('data.avatar');
        $ownedPath = ltrim(substr($ownedUrl, strlen('/storage/')), '/');

        $this->withToken($auth['token'])
            ->patchJson('/api/v1/member/profile', [
                'name' => 'Reader One',
                'avatar' => null,
            ])
            ->assertOk()
            ->assertJsonPath('data.avatar', null);

        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists($ownedPath));

        $external = 'https://cdn.example.com/reader.png';
        $this->withToken($auth['token'])
            ->patchJson('/api/v1/member/profile', [
                'name' => 'Reader One',
                'avatar' => $external,
            ])
            ->assertOk()
            ->assertJsonPath('data.avatar', $external);

        $this->withToken($auth['token'])
            ->patchJson('/api/v1/member/profile', [
                'name' => 'Reader One',
                'avatar' => null,
            ])
            ->assertOk()
            ->assertJsonPath('data.avatar', null);
    }

    public function test_login_updates_last_login_at(): void
    {
        $auth = $this->registerMember();
        Member::query()->whereKey($auth['id'])->update(['last_login_at' => null]);

        $this->postJson('/api/v1/public/member/login', [
            'email' => 'reader-fields@example.com',
            'password' => 'password12',
        ])->assertOk();

        $this->assertNotNull(Member::query()->whereKey($auth['id'])->value('last_login_at'));
    }
}
