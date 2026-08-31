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
