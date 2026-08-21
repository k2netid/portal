<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\Setting;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAndSecurityApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        Setting::set('enable_2fa', true, 'security');
    }

    public function test_user_can_check_2fa_status(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/two-factor/status');

        $response->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.global_enabled', true);
    }

    public function test_user_can_generate_and_enable_2fa(): void
    {
        $admin = $this->createAdminUser();

        // 1. Generate 2FA Secret
        $genResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/two-factor/generate');

        $genResponse->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'secret',
                    'qr_code_url',
                    'backup_codes',
                ],
            ]);

        $secret = $genResponse->json('data.secret');
        $this->assertNotEmpty($secret);

        // 2. Compute valid TOTP code
        $google2fa = new Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);

        // 3. Verify and Enable
        $verifyResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/two-factor/verify', [
                'code' => $validCode,
            ]);

        $verifyResponse->assertOk();
        $this->assertTrue($admin->fresh()->hasTwoFactorEnabled());

        // 4. Verify Status is now enabled
        $statusResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/two-factor/status');

        $statusResponse->assertOk()
            ->assertJsonPath('data.enabled', true);

        // 5. Disable 2FA
        $disableResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/two-factor/disable', [
                'password' => 'password',
            ]);

        $disableResponse->assertOk();
        $this->assertFalse($admin->fresh()->hasTwoFactorEnabled());
    }

    public function test_user_can_view_profile_login_history(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/system/profile/login-history');

        $response->assertOk();
    }
}
