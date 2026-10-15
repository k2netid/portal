<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Mockery;
use Modules\Core\System\Models\Setting;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberGoogleAuthService;
use Modules\Member\Tests\Concerns\SoftensPasswordPolicyForTests;
use Tests\TestCase;

class MemberGoogleAuthTest extends TestCase
{
    use SoftensPasswordPolicyForTests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->softenPasswordPolicyForTests();
        $this->activatePack('member');

        Setting::set('enable_member_google_login', true, 'boolean', 'integrations');
        Setting::set('enable_member_registration', true, 'boolean', 'security');

        config([
            'services.google.client_id' => 'test-google-client-id',
            'services.google.client_secret' => 'test-google-client-secret',
            'services.google.redirect' => 'http://localhost/api/v1/public/member/auth/google/callback',
            'app.frontend_url' => 'http://localhost:5273',
        ]);
    }

    public function test_google_redirect_is_unavailable_without_credentials(): void
    {
        config([
            'services.google.client_id' => '',
            'services.google.client_secret' => '',
        ]);
        Setting::set('member_google_client_id', '', 'string', 'integrations');
        Setting::set('member_google_client_secret', '', 'password', 'integrations');

        $this->getJson('/api/v1/public/member/auth/google/redirect')
            ->assertStatus(503)
            ->assertJsonPath('error_code', 'MEMBER_GOOGLE_UNAVAILABLE');
    }

    public function test_google_credentials_prefer_integrations_settings_over_env(): void
    {
        config([
            'services.google.client_id' => 'env-client-id',
            'services.google.client_secret' => 'env-client-secret',
        ]);
        Setting::set('member_google_client_id', 'ui-client-id', 'string', 'integrations');
        Setting::set('member_google_client_secret', 'ui-client-secret', 'password', 'integrations');

        $credentials = app(MemberGoogleAuthService::class)->resolveCredentials();

        $this->assertSame('ui-client-id', $credentials['client_id']);
        $this->assertSame('ui-client-secret', $credentials['client_secret']);
    }

    public function test_google_callback_creates_member_and_redirects_with_token(): void
    {
        $state = app(MemberGoogleAuthService::class)->storeOAuthState('/member/profile');
        $this->mockGoogleUser('google-uid-1', 'reader@example.com', 'Reader Example');

        $response = $this->get('/api/v1/public/member/auth/google/callback?state='.$state);

        $response->assertRedirect();
        $target = (string) $response->headers->get('Location');
        $this->assertStringContainsString('/member/oauth/callback', $target);
        $this->assertStringContainsString('token=', $target);
        $this->assertStringContainsString('redirect=%2Fmember%2Fprofile', $target);

        $this->assertDatabaseHas('mem_members', [
            'email' => 'reader@example.com',
            'google_id' => 'google-uid-1',
        ]);

        $member = Member::query()->where('email', 'reader@example.com')->first();
        $this->assertNotNull($member?->email_verified_at);
    }

    public function test_google_callback_links_existing_member_by_email(): void
    {
        $member = Member::query()->create([
            'name' => 'Existing',
            'email' => 'existing@example.com',
            'password' => 'Password1!',
            'status' => 'active',
        ]);

        $state = app(MemberGoogleAuthService::class)->storeOAuthState('/member');
        $this->mockGoogleUser('google-uid-2', 'existing@example.com', 'Existing');

        $this->get('/api/v1/public/member/auth/google/callback?state='.$state)
            ->assertRedirect();

        $member->refresh();
        $this->assertSame('google-uid-2', $member->google_id);
    }

    public function test_google_2fa_rejects_invalid_code_and_keeps_pending(): void
    {
        $member = Member::query()->create([
            'name' => 'Secure',
            'email' => 'secure@example.com',
            'password' => 'Password1!',
            'status' => 'active',
            'google_id' => 'google-uid-3',
        ]);

        $pending = app(MemberGoogleAuthService::class)->storePendingTwoFactor($member);

        $this->postJson('/api/v1/public/member/auth/google/2fa', [
            'pending' => $pending,
            'two_factor_code' => '000000',
        ])->assertStatus(422);

        $this->assertTrue(Cache::has('member_google_oauth_2fa:'.$pending));
    }

    public function test_google_2fa_rejects_expired_pending(): void
    {
        $this->postJson('/api/v1/public/member/auth/google/2fa', [
            'pending' => 'missing-pending-token',
            'two_factor_code' => '123456',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'MEMBER_GOOGLE_2FA_EXPIRED');
    }

    private function mockGoogleUser(string $id, string $email, string $name): void
    {
        $user = Mockery::mock(SocialiteUser::class);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('getEmail')->andReturn($email);
        $user->shouldReceive('getName')->andReturn($name);
        $user->shouldReceive('getNickname')->andReturn($name);
        $user->shouldReceive('getAvatar')->andReturn(null);

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($user);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}
