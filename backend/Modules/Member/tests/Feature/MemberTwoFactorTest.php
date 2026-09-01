<?php

declare(strict_types=1);

namespace Modules\Member\Tests\Feature;

use Modules\Core\Security\Models\SecurityLog;
use Modules\Core\System\Models\Setting;
use Modules\Member\Models\Member;
use Modules\Member\Tests\Concerns\SoftensPasswordPolicyForTests;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class MemberTwoFactorTest extends TestCase
{
    use SoftensPasswordPolicyForTests;

    protected function setUp(): void
    {
        parent::setUp();
        $this->softenPasswordPolicyForTests();
        Setting::set('enable_2fa', true, 'boolean', 'security');
        $this->activatePack('member');
    }

    /**
     * @return array{member: Member, token: string, secret: string, backup_codes: list<string>}
     */
    private function registerAndEnableTwoFactor(): array
    {
        $register = $this->postJson('/api/v1/public/member/register', [
            'name' => '2FA Reader',
            'email' => '2fa-reader@example.com',
            'password' => 'password12',
            'password_confirmation' => 'password12',
        ])->assertCreated();

        $token = (string) $register->json('data.token');
        $member = Member::query()->where('email', '2fa-reader@example.com')->firstOrFail();

        $generate = $this->withToken($token)
            ->postJson('/api/v1/member/2fa/generate')
            ->assertOk();

        $this->assertDatabaseHas('mem_member_two_factor', [
            'member_id' => $member->id,
            'enabled' => 0,
        ]);

        $statusResponse = $this->withToken($token)->getJson('/api/v1/member/2fa/status');
        $statusResponse->assertOk();

        $secret = (string) $generate->json('data.secret');
        $backupCodes = $generate->json('data.backup_codes');
        $this->assertIsArray($backupCodes);

        $otp = (new Google2FA)->getCurrentOtp($secret);

        $this->withToken($token)
            ->postJson('/api/v1/member/2fa/verify', ['code' => $otp])
            ->assertOk();

        return [
            'member' => $member->fresh() ?? $member,
            'token' => $token,
            'secret' => $secret,
            'backup_codes' => array_values(array_map('strval', $backupCodes)),
        ];
    }

    public function test_member_can_regenerate_backup_codes_when_2fa_enabled(): void
    {
        $auth = $this->registerAndEnableTwoFactor();

        $response = $this->withToken($auth['token'])
            ->postJson('/api/v1/member/2fa/regenerate-backup-codes', [
                'password' => 'password12',
            ]);

        $response->assertOk();
        $newCodes = $response->json('data.backup_codes');
        $this->assertIsArray($newCodes);
        $this->assertCount(8, $newCodes);
        $this->assertNotSame($auth['backup_codes'], $newCodes);

        $this->assertDatabaseHas('sec_logs', [
            'event_type' => 'member_2fa_backup_codes_regenerated',
        ]);
    }

    public function test_member_login_requires_and_accepts_two_factor_code(): void
    {
        $auth = $this->registerAndEnableTwoFactor();
        $otp = (new Google2FA)->getCurrentOtp($auth['secret']);

        $step1 = $this->postJson('/api/v1/public/member/login', [
            'email' => '2fa-reader@example.com',
            'password' => 'password12',
        ]);

        $step1->assertOk()
            ->assertJsonPath('data.requires_two_factor', true);

        $this->postJson('/api/v1/public/member/login', [
            'email' => '2fa-reader@example.com',
            'password' => 'password12',
            'two_factor_code' => $otp,
        ])->assertOk()
            ->assertJsonPath('data.member.email', '2fa-reader@example.com')
            ->assertJsonStructure(['data' => ['token']]);
    }
}
