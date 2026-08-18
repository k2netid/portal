<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Crm\Sales\Models\Account;
use Modules\Crm\Sales\Models\Lead;
use Modules\Crm\Sales\Models\Opportunity;
use Tests\TestCase;

class CrmExtendedApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->artisan('migrate', ['--path' => 'Modules/Crm/database/migrations', '--force' => true]);
    }

    public function test_lead_convert_creates_contact(): void
    {
        $admin = $this->createAdminUser();
        $lead = Lead::create([
            'first_name' => 'Convert',
            'last_name' => 'Me',
            'email' => 'convert@test.local',
            'company' => 'ACME',
            'status' => 'new',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/crm/leads/{$lead->id}/convert", [
                'create_account' => true,
                'create_opportunity' => true,
                'opportunity_amount' => 1000,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('crm_contacts', ['email' => 'convert@test.local']);
        $this->assertNotNull($lead->fresh()->converted_at);
    }

    public function test_opportunity_stage_patch(): void
    {
        $admin = $this->createAdminUser();
        $account = Account::create(['name' => 'Co', 'status' => 'active']);
        $opp = Opportunity::create(['account_id' => $account->id, 'name' => 'Deal', 'stage' => 'prospecting']);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/crm/opportunities/{$opp->id}/stage", ['stage' => 'negotiation'])
            ->assertOk();

        $this->assertSame('negotiation', $opp->fresh()->stage);
    }

    public function test_reports_summary(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/crm/reports/summary')
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data' => ['counts', 'pipeline_by_stage']]]);
    }

    public function test_bulk_delete_leads(): void
    {
        $admin = $this->createAdminUser();
        $lead = Lead::create(['first_name' => 'X', 'status' => 'new']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/crm/bulk/leads', ['action' => 'delete', 'ids' => [$lead->id]])
            ->assertOk();

        $this->assertSoftDeleted('crm_leads', ['id' => $lead->id]);
    }

    public function test_assignees_list(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/crm/assignees')
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['data']]);
    }

    public function test_lead_search_filter(): void
    {
        $admin = $this->createAdminUser();
        Lead::create(['first_name' => 'UniqueSearch', 'status' => 'new']);
        Lead::create(['first_name' => 'Other', 'status' => 'new']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/crm/leads?search=UniqueSearch')
            ->assertOk();

        $items = $response->json('data.data');
        $this->assertNotEmpty($items);
        $this->assertStringContainsString('UniqueSearch', $items[0]['first_name']);
    }
}
