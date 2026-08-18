<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Crm\Sales\Models\Account;
use Tests\TestCase;

class CrmApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->artisan('migrate', ['--path' => 'Modules/Crm/database/migrations', '--force' => true]);
    }

    public function test_view_only_cannot_create_contact(): void
    {
        $viewer = $this->createUser();
        $viewer->givePermissionTo('view crm');

        $this->actingAs($viewer, 'sanctum')
            ->postJson('/api/v1/crm/contacts', [
                'first_name' => 'Blocked',
            ])
            ->assertForbidden();
    }

    public function test_manage_crm_required_to_create(): void
    {
        $manager = $this->createUser();
        $manager->givePermissionTo(['view crm', 'manage crm']);

        $this->actingAs($manager, 'sanctum')
            ->postJson('/api/v1/crm/contacts', [
                'first_name' => 'Allowed',
                'email' => 'allowed@test.local',
            ])
            ->assertCreated();
    }

    public function test_accounts_list_requires_auth(): void
    {
        Account::create(['name' => 'Hidden', 'status' => 'active']);
        $this->getJson('/api/v1/crm/accounts')->assertUnauthorized();
    }
}
