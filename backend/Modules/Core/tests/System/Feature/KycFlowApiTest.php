<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Core\System\Models\KycSubmission;
use Tests\TestCase;

class KycFlowApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        Storage::fake('local');
    }

    public function test_full_kyc_flow_without_self_promote_level_3(): void
    {
        $user = $this->createUser([
            'email_verified_at' => now(),
            'kyc_level' => 'level_0',
            'onboarding_step' => 0,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/system/profile/kyc/basic', [
                'name' => 'KYC Test User',
                'phone' => '+6281234567890',
                'location' => 'Jakarta',
            ])
            ->assertOk()
            ->assertJsonPath('data.kyc_level', 'level_1');

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/system/profile/kyc/contact')
            ->assertOk()
            ->assertJsonPath('data.kyc_level', 'level_2');

        $file = UploadedFile::fake()->image('ktp.jpg');
        $this->actingAs($user, 'sanctum')
            ->post('/api/v1/manage/system/profile/kyc/documents', [
                'type' => 'id_card',
                'document' => $file,
            ])
            ->assertOk();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/system/profile/kyc/submit')
            ->assertOk()
            ->assertJsonPath('data.submission.status', 'pending_review');

        $user->refresh();
        $this->assertNotSame('level_3', $user->kyc_level);

        $admin = $this->createAdminUser(['kyc_level' => 'level_1']);
        $submission = KycSubmission::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($submission);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/manage/system/kyc/submissions/{$submission->id}/approve")
            ->assertOk();

        $user->refresh();
        $this->assertSame('level_3', $user->kyc_level);
    }

    public function test_legacy_kyc_step_cannot_set_level_3(): void
    {
        $user = $this->createUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/manage/system/profile/kyc/step', [
                'step' => 3,
                'level' => 'level_3',
            ])
            ->assertStatus(422);
    }
}
