<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Publishing\Contracts\NewsletterSampleDataPortInterface;
use Modules\Intelligence\Newsletter\Models\NewsletterSubscriber;
use Tests\TestCase;

class NewsletterSampleDataPortTest extends TestCase
{
    use RefreshDatabase;

    public function test_port_upserts_subscriber_without_content_importing_intelligence_model(): void
    {
        $port = app(NewsletterSampleDataPortInterface::class);

        $port->upsertSubscriberByEmail('port-test@example.com', [
            'name' => 'Port Test',
            'status' => 'subscribed',
        ]);

        $this->assertDatabaseHas('nwl_subscribers', [
            'email' => 'port-test@example.com',
            'name' => 'Port Test',
        ]);

        $this->assertInstanceOf(NewsletterSubscriber::class, NewsletterSubscriber::query()->where('email', 'port-test@example.com')->first());
    }
}
