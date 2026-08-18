<?php

namespace Modules\Content\Forms\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Content\Forms\Models\Form;
use Modules\Content\Forms\Models\FormAnalytics;
use Modules\Content\Forms\Models\FormSubmission;
use Modules\Core\System\Models\User;
use Tests\Helpers\TestHelpers;
use Tests\TestCase;

class FormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    /**
     * Test admin can list all forms.
     */
    public function test_admin_can_list_all_forms(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        Form::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/manage/forms');

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test admin can create form.
     */
    public function test_admin_can_create_form(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $formData = [
            'name' => 'Contact Form '.uniqid(),
            'slug' => 'contact-form-'.uniqid(),
            'description' => 'A contact form',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/manage/forms', $formData);

        TestHelpers::assertApiSuccess($response, 201);
        $this->assertDatabaseHas('frm_forms', [
            'name' => $formData['name'],
            'slug' => $formData['slug'],
        ]);
    }

    /**
     * Test form creation requires name.
     */
    public function test_form_creation_requires_name(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $response = $this->postJson('/api/v1/manage/forms', []);

        TestHelpers::assertApiValidationError($response);
        $response->assertJsonValidationErrors(['name']);
    }

    /**
     * Test admin can view form details.
     */
    public function test_admin_can_view_form_details(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();

        $response = $this->getJson("/api/v1/manage/forms/{$form->id}");

        TestHelpers::assertApiSuccess($response);
        $response->assertJson([
            'data' => [
                'id' => $form->id,
                'name' => $form->name,
            ],
        ]);
    }

    /**
     * Test admin can update form.
     */
    public function test_admin_can_update_form(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();

        $response = $this->putJson("/api/v1/manage/forms/{$form->id}", [
            'name' => 'Updated Form Name',
        ]);

        TestHelpers::assertApiSuccess($response);
        $this->assertDatabaseHas('frm_forms', [
            'id' => $form->id,
            'name' => 'Updated Form Name',
        ]);
    }

    /**
     * Test admin can delete form.
     */
    public function test_admin_can_delete_form(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();

        $response = $this->deleteJson("/api/v1/manage/forms/{$form->id}");

        TestHelpers::assertApiSuccess($response);
        $this->assertSoftDeleted('frm_forms', [
            'id' => $form->id,
        ]);
    }

    /**
     * Test admin can duplicate form and copy fields.
     */
    public function test_admin_can_duplicate_form(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();
        $form->fields()->create([
            'name' => 'full_name',
            'label' => 'Name',
            'type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $form->fields()->create([
            'name' => 'email_addr',
            'label' => 'Email',
            'type' => 'email',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        $response = $this->postJson("/api/v1/manage/forms/{$form->id}/duplicate", [
            'title' => $form->name.' (Copy)',
            'slug' => $form->slug.'-copy',
        ]);

        TestHelpers::assertApiSuccess($response, 201);
        $newId = (string) $response->json('data.id');
        $this->assertNotSame($form->id, $newId);
        $this->assertSame(2, Form::query()->findOrFail($newId)->fields()->count());
    }

    /**
     * Test admin can add, reorder, update, and delete form fields.
     */
    public function test_admin_can_manage_frm_form_fields(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();

        $a = $this->postJson("/api/v1/manage/forms/{$form->id}/fields", [
            'label' => 'First',
            'type' => 'text',
            'is_required' => false,
        ]);
        TestHelpers::assertApiSuccess($a, 201);
        $idA = (string) $a->json('data.id');

        $b = $this->postJson("/api/v1/manage/forms/{$form->id}/fields", [
            'label' => 'Second',
            'type' => 'textarea',
            'is_required' => true,
        ]);
        TestHelpers::assertApiSuccess($b, 201);
        $idB = (string) $b->json('data.id');

        $reorder = $this->postJson("/api/v1/manage/forms/{$form->id}/reorder-fields", [
            'order' => [$idB, $idA],
        ]);
        TestHelpers::assertApiSuccess($reorder);
        $this->assertSame(1, (int) $form->fields()->where('id', $idB)->value('sort_order'));
        $this->assertSame(2, (int) $form->fields()->where('id', $idA)->value('sort_order'));

        $upd = $this->putJson("/api/v1/manage/forms/{$form->id}/fields/{$idA}", [
            'label' => 'First updated',
            'is_required' => true,
        ]);
        TestHelpers::assertApiSuccess($upd);
        $this->assertSame('First updated', (string) $form->fields()->where('id', $idA)->value('label'));

        $del = $this->deleteJson("/api/v1/manage/forms/{$form->id}/fields/{$idB}");
        TestHelpers::assertApiSuccess($del);
        $this->assertSame(1, $form->fields()->count());
    }

    /**
     * Test admin can view form submissions.
     */
    public function test_admin_can_view_frm_form_submissions(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();
        FormSubmission::factory()->count(3)->create(['form_id' => $form->id]);

        $response = $this->getJson("/api/v1/manage/forms/{$form->id}/submissions");

        TestHelpers::assertApiSuccess($response);
    }

    /**
     * Test form submission statistics match DB status enum (new/read/archived).
     */
    public function test_form_submission_statistics_all_time_syncs_with_database(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();
        FormSubmission::factory()->for($form)->create(['status' => 'new']);
        FormSubmission::factory()->for($form)->create(['status' => 'new']);
        FormSubmission::factory()->for($form)->read()->create();
        FormSubmission::factory()->for($form)->archived()->create();

        $response = $this->getJson("/api/v1/manage/forms/{$form->id}/submissions/statistics");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonPath('data.total', 4);
        $response->assertJsonPath('data.new', 2);
        $response->assertJsonPath('data.read', 1);
        $response->assertJsonPath('data.archived', 1);
    }

    /**
     * Test analytics-style statistics include chart payloads when date range is sent.
     */
    public function test_form_submission_statistics_with_range_returns_chart_data(): void
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');

        $form = Form::factory()->create();
        $form->fields()->create([
            'name' => 'source',
            'label' => 'Source',
            'type' => 'select',
            'options' => [
                ['label' => 'A', 'value' => 'a'],
                ['label' => 'B', 'value' => 'b'],
            ],
            'sort_order' => 1,
            'is_required' => false,
        ]);
        FormSubmission::factory()->for($form)->create([
            'status' => 'new',
            'data' => ['source' => 'a', 'name' => 'X'],
            'created_at' => now()->subDay(),
        ]);

        FormAnalytics::query()->create([
            'form_id' => $form->id,
            'date' => now()->subDay()->toDateString(),
            'views' => 12,
            'starts' => 5,
            'submissions' => 1,
        ]);

        $response = $this->getJson("/api/v1/manage/forms/{$form->id}/submissions/statistics?days=7&aggregate_field=source");

        TestHelpers::assertApiSuccess($response);
        $response->assertJsonStructure([
            'data' => [
                'total',
                'growth',
                'previous_total',
                'all_time_total',
                'daily_stats',
                'daily_views_stats',
                'range_views_total',
                'range_starts_total',
                'hourly_stats',
                'weekly_stats',
                'chartable_fields',
                'field_distribution',
            ],
        ]);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.field_distribution')));
        $this->assertSame(12, (int) $response->json('data.range_views_total'));
        $this->assertSame(5, (int) $response->json('data.range_starts_total'));
        $this->assertGreaterThanOrEqual(1, count($response->json('data.daily_views_stats')));
    }

    /**
     * Test public can view active form.
     */
    public function test_public_can_view_active_form(): void
    {
        $form = Form::factory()->create(['is_active' => true]);

        $response = $this->getJson("/api/v1/public/forms/{$form->slug}");

        $response->assertStatus(200);
        $response->assertJson([
            'name' => $form->name,
        ]);
    }

    /**
     * Test public can submit form.
     */
    public function test_public_can_submit_form(): void
    {
        $form = Form::factory()->create(['is_active' => true]);

        $form->fields()->createMany([
            [
                'name' => 'name',
                'label' => 'Name',
                'type' => 'text',
                'is_required' => true,
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
                'is_required' => true,
            ],
        ]);

        $response = $this->postJson("/api/v1/public/forms/{$form->slug}/submit", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        TestHelpers::assertApiSuccess($response, 201);
    }

    /**
     * Test public form submit stores uploaded file metadata on disk.
     */
    public function test_public_can_submit_form_with_file_field(): void
    {
        Storage::fake('public');

        $form = Form::factory()->create(['is_active' => true]);

        $form->fields()->createMany([
            [
                'name' => 'title',
                'label' => 'Title',
                'type' => 'text',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'doc',
                'label' => 'Document',
                'type' => 'file',
                'is_required' => true,
                'sort_order' => 2,
            ],
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 50, 'application/pdf');

        $response = $this->post("/api/v1/public/forms/{$form->slug}/submit", [
            'title' => 'Hello',
            'doc' => $file,
        ]);

        TestHelpers::assertApiSuccess($response, 201);

        $submission = $form->submissions()->first();
        $this->assertNotNull($submission);
        /** @var array<string, mixed> $data */
        $data = $submission->data;
        $this->assertArrayHasKey('doc', $data);
        $this->assertIsArray($data['doc']);
        $this->assertSame('upload', $data['doc']['type'] ?? null);
        $this->assertArrayHasKey('path', $data['doc']);
        Storage::disk('public')->assertExists((string) $data['doc']['path']);
    }

    /**
     * Test form submission validates required fields.
     */
    public function test_form_submission_validates_required_fields(): void
    {
        $form = Form::factory()->create(['is_active' => true]);

        $form->fields()->create([
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'is_required' => true,
        ]);

        $response = $this->postJson("/api/v1/public/forms/{$form->slug}/submit", []);

        TestHelpers::assertApiValidationError($response);
    }

    /**
     * Test unauthenticated user cannot manage forms.
     */
    public function test_unauthenticated_user_cannot_manage_forms(): void
    {
        $response = $this->postJson('/api/v1/manage/forms', [
            'name' => 'Test Form',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test user without permission cannot manage forms.
     */
    public function test_user_without_permission_cannot_manage_forms(): void
    {
        $user = $this->createUser();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/manage/forms', [
            'name' => 'Test Form',
        ]);

        $response->assertStatus(403);
    }
}
