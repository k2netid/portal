<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\System\Models\ContentType;
use Modules\Core\System\Models\DynamicRecord;
use Modules\Core\System\Models\User;
use Tests\TestCase;

class DynamicCckTest extends TestCase
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
     * Test creating, reading, updating, and deleting dynamic content type schemas.
     */
    public function test_cck_schema_management_crud(): void
    {
        // 1. Create content type schema (Visual Builder Simulation)
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
            ->postJson('/api/v1/manage/infra/cck/types', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sys_content_types', [
            'slug' => 'portfolios',
            'name' => 'Portfolio Item',
        ]);

        $typeId = $response->json('data.id');
        $this->assertNotNull($typeId);

        // 2. List content types
        $listResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/cck/types');
        $listResponse->assertStatus(200);
        $listResponse->assertJsonFragment(['slug' => 'portfolios']);

        $bySlugResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/manage/infra/cck/types/by-slug/portfolios');
        $bySlugResponse->assertStatus(200)
            ->assertJsonPath('data.slug', 'portfolios');

        // 3. Show content type
        $showResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/infra/cck/types/{$typeId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.name', 'Portfolio Item');

        // 4. Update content type
        $updatePayload = array_merge($payload, [
            'name' => 'Developer Portfolio',
        ]);
        $updateResponse = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/manage/infra/cck/types/{$typeId}", $updatePayload);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('data.name', 'Developer Portfolio');

        // 5. Delete content type
        $deleteResponse = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/manage/infra/cck/types/{$typeId}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('sys_content_types', ['id' => $typeId]);
    }

    /**
     * Test the Instant API Generation: input validation, record insertion, paginated search, and partial updates.
     */
    public function test_instant_api_generation_crud_and_validation(): void
    {
        // 1. Manually insert an active content type schema into database
        $contentType = ContentType::create([
            'name' => 'Product catalog',
            'slug' => 'products',
            'description' => 'E-commerce product items',
            'is_active' => true,
            'fields' => [
                [
                    'name' => 'Product Name',
                    'slug' => 'name',
                    'type' => 'text',
                    'is_required' => true,
                ],
                [
                    'name' => 'Price',
                    'slug' => 'price',
                    'type' => 'number',
                    'is_required' => true,
                ],
                [
                    'name' => 'Release Date',
                    'slug' => 'released_on',
                    'type' => 'date',
                    'is_required' => false,
                ],
            ],
        ]);

        // 2. Attempt store validation failure (missing required title/price)
        $invalidResponse = $this->postJson('/api/v1/dynamic/products', [
            'released_on' => '2026-05-19',
        ]);
        $invalidResponse->assertStatus(422);
        $invalidResponse->assertJsonValidationErrors(['name', 'price']);

        // 3. Attempt store validation failure (invalid type for price)
        $invalidTypeResponse = $this->postJson('/api/v1/dynamic/products', [
            'name' => 'Super Gadget',
            'price' => 'not-a-number',
        ]);
        $invalidTypeResponse->assertStatus(422);
        $invalidTypeResponse->assertJsonValidationErrors(['price']);

        // 4. Store a successful valid dynamic EAV record
        $validPayload = [
            'name' => 'Stellar Smartphone',
            'price' => 999.99,
            'released_on' => '2026-05-19',
        ];
        $successResponse = $this->postJson('/api/v1/dynamic/products', $validPayload);
        $successResponse->assertStatus(201);
        $recordId = $successResponse->json('data.id');
        $this->assertNotNull($recordId);

        $this->assertDatabaseHas('sys_dynamic_records', [
            'id' => $recordId,
            'content_type_id' => $contentType->id,
        ]);

        // 5. GET index of dynamic products
        $indexResponse = $this->getJson('/api/v1/dynamic/products');
        $indexResponse->assertStatus(200);
        $indexResponse->assertJsonFragment(['name' => 'Stellar Smartphone']);

        // 6. GET index filtering by keyword search (MySQL/SQLite JSON search query test)
        $searchMatchResponse = $this->getJson('/api/v1/dynamic/products?search=Smartphone');
        $searchMatchResponse->assertStatus(200);
        $searchMatchResponse->assertJsonFragment(['name' => 'Stellar Smartphone']);

        $searchMissResponse = $this->getJson('/api/v1/dynamic/products?search=NonExistent');
        $searchMissResponse->assertStatus(200);
        $this->assertCount(0, $searchMissResponse->json('data.data'));

        // 7. GET show single dynamic record
        $showResponse = $this->getJson("/api/v1/dynamic/products/{$recordId}");
        $showResponse->assertStatus(200);
        $showResponse->assertJsonPath('data.data.name', 'Stellar Smartphone');

        // 8. PUT partially update dynamic record
        $updateResponse = $this->putJson("/api/v1/dynamic/products/{$recordId}", [
            'name' => 'Stellar Smartphone Pro',
            'price' => 1099.99,
        ]);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJsonPath('data.data.name', 'Stellar Smartphone Pro');
        $updateResponse->assertJsonPath('data.data.price', 1099.99);

        // 9. DELETE single dynamic record
        $deleteResponse = $this->deleteJson("/api/v1/dynamic/products/{$recordId}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('sys_dynamic_records', ['id' => $recordId]);
    }

    public function test_cck_validation_rules_endpoint_and_select_field(): void
    {
        $payload = [
            'name' => 'Lead Form',
            'slug' => 'leads',
            'description' => 'Lead capture',
            'fields' => [
                [
                    'name' => 'Email',
                    'slug' => 'email',
                    'type' => 'email',
                    'is_required' => true,
                ],
                [
                    'name' => 'Source',
                    'slug' => 'source',
                    'type' => 'select',
                    'is_required' => true,
                    'options' => ['web', 'referral'],
                ],
            ],
        ];

        $create = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/manage/infra/cck/types', $payload);
        $create->assertStatus(201);
        $typeId = $create->json('data.id');

        $rulesResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/manage/infra/cck/types/{$typeId}/validation-rules");
        $rulesResponse->assertStatus(200)
            ->assertJsonPath('data.validation_rules.email', 'required|email')
            ->assertJsonPath('data.validation_rules.source', 'required|string|in:web,referral');

        $invalid = $this->postJson('/api/v1/dynamic/leads', [
            'email' => 'not-an-email',
            'source' => 'invalid',
        ]);
        $invalid->assertStatus(422);

        $valid = $this->postJson('/api/v1/dynamic/leads', [
            'email' => 'user@example.com',
            'source' => 'web',
        ]);
        $valid->assertStatus(201);
    }
}
