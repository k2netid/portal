<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Modules\Core\System\Contracts\OutboundMailPortInterface;
use Modules\Member\Models\Member;
use Tests\TestCase;

class MemberAccountLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->activatePack('member');
    }

    /**
     * @return array{token: string, id: string}
     */
    private function registerMember(string $email = 'reader@example.com'): array
    {
        $response = $this->postJson('/api/v1/public/member/register', [
            'name' => 'Reader One',
            'email' => $email,
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ]);

        $response->assertCreated();

        return [
            'token' => (string) $response->json('data.token'),
            'id' => (string) $response->json('data.member.id'),
        ];
    }

    public function test_forgot_password_sends_link_and_reset_works(): void
    {
        $this->activatePack('mail');
        $html = null;
        $this->mock(OutboundMailPortInterface::class, function ($mock) use (&$html): void {
            $mock->shouldReceive('send')
                ->atLeast()
                ->once()
                ->andReturnUsing(function (...$args) use (&$html): array {
                    $body = is_string($args[2] ?? null) ? $args[2] : '';
                    if (str_contains($body, 'Choose a new password') || str_contains($body, 'token=')) {
                        $html = $body;
                    }

                    return ['status' => 'sent'];
                });
        });

        $this->registerMember();

        $this->postJson('/api/v1/public/member/forgot-password', [
            'email' => 'reader@example.com',
        ])->assertOk();

        $this->assertIsString($html);
        $this->assertMatchesRegularExpression('/token=([^&"]+)/', (string) $html);
        preg_match('/token=([^&"]+)/', (string) $html, $tokenMatch);
        preg_match('/email=([^&"]+)/', (string) $html, $emailMatch);
        $token = urldecode($tokenMatch[1]);
        $email = urldecode($emailMatch[1]);

        $this->postJson('/api/v1/public/member/reset-password', [
            'email' => $email,
            'token' => $token,
            'password' => 'newpass123',
            'password_confirmation' => 'newpass123',
        ])->assertOk();

        $this->postJson('/api/v1/public/member/login', [
            'email' => 'reader@example.com',
            'password' => 'newpass123',
        ])->assertOk();
    }

    public function test_forgot_password_does_not_reveal_missing_email(): void
    {
        $this->postJson('/api/v1/public/member/forgot-password', [
            'email' => 'nobody@example.com',
        ])->assertOk();
    }

    public function test_member_can_request_email_change_and_confirm(): void
    {
        $this->activatePack('mail');
        $html = null;
        $this->mock(OutboundMailPortInterface::class, function ($mock) use (&$html): void {
            $mock->shouldReceive('send')
                ->atLeast()
                ->once()
                ->andReturnUsing(function (...$args) use (&$html): array {
                    $body = is_string($args[2] ?? null) ? $args[2] : '';
                    if (str_contains($body, 'Confirm your new email') || str_contains($body, 'confirm-email-change')) {
                        $html = $body;
                    }

                    return ['status' => 'sent'];
                });
        });

        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->putJson('/api/v1/member/email', [
                'email' => 'newreader@example.com',
                'current_password' => 'password12',
            ])
            ->assertOk()
            ->assertJsonPath('data.pending_email', 'newreader@example.com');

        $member = Member::query()->findOrFail($auth['id']);
        $this->assertSame('newreader@example.com', $member->pending_email);
        $this->assertIsString($html);
        preg_match('/href="([^"]+)"/', (string) $html, $matches);
        $url = html_entity_decode($matches[1], ENT_QUOTES);

        $this->withoutToken()
            ->getJson($url)
            ->assertOk()
            ->assertJsonPath('data.email_verified', true);

        $member->refresh();
        $this->assertSame('newreader@example.com', $member->email);
        $this->assertNull($member->pending_email);
        $this->assertNotNull($member->email_verified_at);
        $this->assertSame(0, $member->tokens()->count());
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertNull(\Laravel\Sanctum\PersonalAccessToken::findToken($auth['token']));

        // Old email no longer authenticates; new email does (tokens were revoked).
        $this->postJson('/api/v1/public/member/login', [
            'email' => 'reader@example.com',
            'password' => 'password12',
        ])->assertUnauthorized();

        $this->postJson('/api/v1/public/member/login', [
            'email' => 'newreader@example.com',
            'password' => 'password12',
        ])->assertOk();
    }

    public function test_email_change_rejects_wrong_password(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->putJson('/api/v1/member/email', [
                'email' => 'newreader@example.com',
                'current_password' => 'wrong-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_member_can_delete_account(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->deleteJson('/api/v1/member/account', [
                'current_password' => 'password12',
                'confirm' => 'DELETE',
            ])
            ->assertOk();

        $this->assertDatabaseMissing('mem_members', ['id' => $auth['id']]);

        $this->postJson('/api/v1/public/member/login', [
            'email' => 'reader@example.com',
            'password' => 'password12',
        ])->assertUnauthorized();
    }

    public function test_delete_account_requires_confirm_phrase(): void
    {
        $auth = $this->registerMember();

        $this->withToken($auth['token'])
            ->deleteJson('/api/v1/member/account', [
                'current_password' => 'password12',
                'confirm' => 'delete',
            ])
            ->assertUnprocessable();
    }

    public function test_demo_seeder_creates_verified_reader_when_flag_on(): void
    {
        config(['install.seed_demo' => true]);
        \Modules\Member\Database\Seeders\MemberDemoSeeder::ensure();

        $member = Member::query()->where('email', 'reader@example.com')->first();
        $this->assertNotNull($member);
        $this->assertNotNull($member->email_verified_at);

        $this->postJson('/api/v1/public/member/login', [
            'email' => 'reader@example.com',
            'password' => 'password12',
        ])->assertOk();
    }
}
