<?php

declare(strict_types=1);

namespace Modules\Intelligence\Newsletter\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Intelligence\Newsletter\Models\NewsletterSubscriber;
use Tests\TestCase;

class NewsletterSubscribersIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionsAndRoles();
    }

    public function test_subscribers_index_with_empty_q_succeeds(): void
    {
        $admin = $this->createAdminUser();

        NewsletterSubscriber::query()->create([
            'email' => 'alpha@example.com',
            'name' => 'Alpha',
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/newsletter/subscribers?q=&page=1&per_page=15')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_subscribers_index_search_by_email(): void
    {
        $admin = $this->createAdminUser();

        NewsletterSubscriber::query()->create([
            'email' => 'unique-find@example.com',
            'name' => 'Find Me',
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/manage/newsletter/subscribers?q=unique-find&per_page=15')
            ->assertOk()
            ->assertJsonPath('data.data.0.email', 'unique-find@example.com');
    }
}
