<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
        $this->artisan('migrate', ['--path' => 'Modules/Crm/database/migrations', '--force' => true]);
    }

    public function test_pipeline_crud(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/crm/pipelines', ['name' => 'Partner', 'slug' => 'partner', 'stages' => ['new', 'won']])
            ->assertCreated();
        $this->assertDatabaseHas('crm_pipelines', ['name' => 'Partner']);
    }

    public function test_custom_field_definition(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/crm/custom-field-definitions', [
                'entity_type' => 'lead', 'field_key' => 'budget', 'label' => 'Budget', 'field_type' => 'number',
            ])
            ->assertCreated();
    }
}
