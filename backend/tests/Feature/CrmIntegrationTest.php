<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Content\Forms\Events\FormSubmitted;
use Modules\Content\Forms\Models\Form;
use Modules\Content\Forms\Models\FormField;
use Modules\Crm\Events\CrmSearchIndexChanged;
use Modules\Crm\Events\LeadConverted;
use Modules\Crm\Sales\Models\Account;
use Modules\Crm\Sales\Models\Lead;
use Modules\Crm\Sales\Services\CrmEntityExistsService;
use Tests\TestCase;

class CrmIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->artisan('migrate', ['--path' => 'Modules/Crm/database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Content/database/migrations', '--force' => true]);
        $this->artisan('migrate', ['--path' => 'Modules/Intelligence/database/migrations', '--force' => true]);
    }

    public function test_form_submitted_creates_lead_when_capture_enabled(): void
    {
        $form = Form::create([
            'name' => 'Contact',
            'slug' => 'contact-'.uniqid(),
            'is_active' => true,
            'settings' => [
                'crm_lead_capture' => true,
                'crm_lead_mapping' => ['email' => 'email', 'first_name' => 'name'],
            ],
        ]);
        FormField::create([
            'form_id' => $form->id,
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        FormField::create([
            'form_id' => $form->id,
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        $submission = $form->submissions()->create([
            'data' => ['email' => 'lead@form.test', 'name' => 'Form User'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);

        event(new FormSubmitted($form, $submission, ['email' => 'lead@form.test', 'name' => 'Form User']));

        $this->assertDatabaseHas('crm_leads', [
            'email' => 'lead@form.test',
            'first_name' => 'Form User',
        ]);
    }

    public function test_lead_save_dispatches_search_index_event(): void
    {
        Event::fake([CrmSearchIndexChanged::class]);

        $lead = Lead::create([
            'first_name' => 'Search',
            'last_name' => 'Me',
            'email' => 'search@test.local',
            'status' => 'new',
        ]);

        Event::assertDispatched(CrmSearchIndexChanged::class, function (CrmSearchIndexChanged $e) use ($lead): bool {
            return $e->entityKind === 'lead' && $e->entityId === $lead->id && $e->action === 'upsert';
        });
    }

    public function test_crm_search_index_changed_syncs_lead(): void
    {
        $lead = Lead::create([
            'first_name' => 'Search',
            'last_name' => 'Me',
            'email' => 'search@test.local',
            'status' => 'new',
        ]);

        event(new CrmSearchIndexChanged('lead', (string) $lead->id, 'upsert'));

        $this->assertDatabaseHas('srch_indexes', [
            'searchable_id' => $lead->id,
            'type' => 'crm_lead',
        ]);
    }

    public function test_crm_entity_exists_ports(): void
    {
        $account = Account::create(['name' => 'Co', 'status' => 'active']);
        $service = app(CrmEntityExistsService::class);

        $this->assertTrue($service->accountExists((string) $account->id));
        $this->assertFalse($service->accountExists('00000000-0000-0000-0000-000000000099'));
    }

    public function test_lead_convert_dispatches_event(): void
    {
        Event::fake([LeadConverted::class]);

        $admin = $this->createAdminUser();
        $lead = Lead::create([
            'first_name' => 'E',
            'email' => 'e@test.local',
            'status' => 'new',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/crm/leads/{$lead->id}/convert", ['create_account' => true])
            ->assertOk();

        Event::assertDispatched(LeadConverted::class);
    }
}
