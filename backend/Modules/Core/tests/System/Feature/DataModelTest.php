<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Models\DynamicRecord;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class DataModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->admin = $this->createAdminUser();

        ContentType::truncate();
        DynamicRecord::truncate();
    }

    /**
     * Test creating, reading, updating, and deleting dynamic data model schemas.
     */
    public function test_data_model_schema_management_crud(): void
    {
        // 1. Create data model schema (Visual Builder Simulation)
        $payload = [
            'name' => 'Portfolio Item',
            'slug' => 'portfolios',
            'description' => 'A custom schema for showing developer projects',
            'fields' => [
                [
                    'name' => 'Project Name',
                    'slug' => 'project_name',
                    'type' => 'text',
                    'is_required' => true,
                ],
                [
                    'name' => 'Budget',
                    'slug' => 'budget',
                    'type' => 'number',
                    'is_required' => true,
                ],
                [
                    'name' => 'Is Completed',
                    'slug' => 'is_completed',
                    'type' => 'boolean',
                    'is_required' => false,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/models/types', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sys_content_types', [
            'slug' => 'portfolios',
            'name' => 'Portfolio Item',
        ]);

        $typeId = $response->json('data.id');
        $this->assertNotNull($typeId);

        // 2. List data models
        $listResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/models/types');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonFragment(['slug' => 'portfolios']);

        $bySlugResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/models/types/by-slug/portfolios');
        $bySlugResponse->assertStatus(200);
        $bySlugResponse->assertJsonPath('data.name', 'Portfolio Item');

        // 3. Show data model
        $showResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/infra/models/types/{$typeId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.slug', 'portfolios');

        // 4. Update data model
        $updatePayload = $payload;
        $updatePayload['name'] = 'Portfolio Item V2';
        $updateResponse = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/manage/infra/models/types/{$typeId}", $updatePayload);
        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('sys_content_types', ['name' => 'Portfolio Item V2']);

        // 5. Delete data model
        $deleteResponse = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/manage/infra/models/types/{$typeId}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('sys_content_types', ['id' => $typeId]);
    }

    /**
     * Test instant EAV CRUD operations, validation rules, and SQLite/MySQL dynamic searching.
     */
    public function test_dynamic_records_instant_crud_and_validation(): void
    {
        $contentType = ContentType::create([
            'name' => 'Product Inventory',
            'slug' => 'products',
            'is_active' => true,
            'fields' => [
                [
                    'name' => 'SKU Code',
                    'slug' => 'sku',
                    'type' => 'text',
                    'is_required' => true,
                ],
                [
                    'name' => 'Price USD',
                    'slug' => 'price',
                    'type' => 'number',
                    'is_required' => true,
                ],
                [
                    'name' => 'In Stock',
                    'slug' => 'in_stock',
                    'type' => 'boolean',
                    'is_required' => false,
                ],
            ],
        ]);

        // 1. Test POST validation failure (Missing required SKU)
        $invalidResponse = $this->postJson('/api/v1/dynamic/products', [
            'price' => 99.50,
        ]);
        $invalidResponse->assertStatus(422);
        $invalidResponse->assertJsonValidationErrors(['sku']);

        // 2. Test successful creation
        $createResponse = $this->postJson('/api/v1/dynamic/products', [
            'sku' => 'LAPTOP-PRO-16',
            'price' => 1899.99,
            'in_stock' => true,
        ]);
        $createResponse->assertStatus(201);
        $recordId = $createResponse->json('data.id');
        $this->assertNotNull($recordId);

        $this->assertDatabaseHas('sys_dynamic_records', [
            'id' => $recordId,
            'content_type_id' => $contentType->id,
        ]);

        // 3. Create second record for search testing
        $this->postJson('/api/v1/dynamic/products', [
            'sku' => 'KEYBOARD-MECH-RGB',
            'price' => 129.00,
            'in_stock' => false,
        ]);

        // 4. Test GET list with search query
        $searchResponse = $this->getJson('/api/v1/dynamic/products?search=LAPTOP');
        $searchResponse->assertStatus(200);
        $this->assertCount(1, $searchResponse->json('data.data'));
        $searchResponse->assertJsonPath('data.data.0.data.sku', 'LAPTOP-PRO-16');

        // 5. Test GET show
        $showResponse = $this->getJson("/api/v1/dynamic/products/{$recordId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.data.price', 1899.99);

        // 6. Test PUT update
        $updateResponse = $this->putJson("/api/v1/dynamic/products/{$recordId}", [
            'price' => 1799.50,
        ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('data.data.price', 1799.50);
        $updateResponse->assertJsonPath('data.data.sku', 'LAPTOP-PRO-16');

        // 7. Test DELETE
        $deleteResponse = $this->deleteJson("/api/v1/dynamic/products/{$recordId}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('sys_dynamic_records', ['id' => $recordId]);
    }

    /**
     * Test validation rules endpoint and select field options constraint.
     */
    public function test_data_model_validation_rules_endpoint_and_select_field(): void
    {
        $payload = [
            'name' => 'Job Application',
            'slug' => 'job_applications',
            'fields' => [
                [
                    'name' => 'Applicant Name',
                    'slug' => 'applicant_name',
                    'type' => 'text',
                    'is_required' => true,
                ],
                [
                    'name' => 'Status',
                    'slug' => 'status',
                    'type' => 'select',
                    'is_required' => true,
                    'options' => ['pending', 'interviewing', 'accepted', 'rejected'],
                ],
                [
                    'name' => 'Resume Attachment',
                    'slug' => 'resume_url',
                    'type' => 'url',
                    'is_required' => false,
                ],
            ],
        ];

        $createTypeResponse = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/models/types', $payload);
        $createTypeResponse->assertStatus(201);
        $typeId = $createTypeResponse->json('data.id');

        $rulesResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/infra/models/types/{$typeId}/validation-rules");
        $rulesResponse->assertStatus(200);
        $rulesResponse->assertJsonPath('data.validation_rules.status', 'required|string|in:pending,interviewing,accepted,rejected');
        $rulesResponse->assertJsonPath('data.validation_rules.resume_url', 'nullable|url');

        // Test dynamic record with invalid select option
        $invalidSelectRes = $this->postJson('/api/v1/dynamic/job_applications', [
            'applicant_name' => 'Budi Setiawan',
            'status' => 'invalid_status_value',
        ]);
        $invalidSelectRes->assertStatus(422);
        $invalidSelectRes->assertJsonValidationErrors(['status']);

        // Test dynamic record with valid select option
        $validSelectRes = $this->postJson('/api/v1/dynamic/job_applications', [
            'applicant_name' => 'Budi Setiawan',
            'status' => 'interviewing',
            'resume_url' => 'https://example.com/resumes/budi.pdf',
        ]);
        $validSelectRes->assertStatus(201);
    }

    /**
     * Test relational content modeling and automatic relation hydration.
     */
    public function test_relational_content_modeling_and_hydration(): void
    {
        // 1. Create Author Model
        $authorType = ContentType::create([
            'name' => 'Author',
            'slug' => 'authors',
            'is_active' => true,
            'fields' => [
                ['name' => 'Full Name', 'slug' => 'name', 'type' => 'text', 'is_required' => true],
                ['name' => 'Email', 'slug' => 'email', 'type' => 'email', 'is_required' => true],
            ],
        ]);

        // 2. Create Book Model referencing Author
        $bookType = ContentType::create([
            'name' => 'Book',
            'slug' => 'books',
            'is_active' => true,
            'fields' => [
                ['name' => 'Title', 'slug' => 'title', 'type' => 'text', 'is_required' => true],
                ['name' => 'Price', 'slug' => 'price', 'type' => 'number', 'is_required' => true],
                ['name' => 'Author', 'slug' => 'author_id', 'type' => 'relation', 'target_type' => 'authors', 'relation_mode' => 'single', 'is_required' => true],
            ],
        ]);

        // 3. Create Author Record
        $authorRes = $this->postJson('/api/v1/dynamic/authors', [
            'name' => 'Pramoedya Ananta Toer',
            'email' => 'pram@example.com',
        ]);
        $authorRes->assertStatus(201);
        $authorId = $authorRes->json('data.id');

        // 4. Create Book Record linking to Author
        $bookRes = $this->postJson('/api/v1/dynamic/books', [
            'title' => 'Bumi Manusia',
            'price' => 125000,
            'author_id' => $authorId,
        ]);
        $bookRes->assertStatus(201);
        $bookId = $bookRes->json('data.id');

        // 5. Verify Relational Hydration on GET Book
        $getBookRes = $this->getJson("/api/v1/dynamic/books/{$bookId}");
        $getBookRes->assertStatus(200);
        $getBookRes->assertJsonPath('data._relations.author_id.data.name', 'Pramoedya Ananta Toer');

        // 6. Verify Relational Hydration on GET Books List
        $listBooksRes = $this->getJson('/api/v1/dynamic/books');
        $listBooksRes->assertStatus(200);
        $listBooksRes->assertJsonPath('data.data.0._relations.author_id.data.name', 'Pramoedya Ananta Toer');
    }
}
