<?php

declare(strict_types=1);

namespace Modules\Newsletter\Tests\Feature;

use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsletterPackGateTest extends TestCase
{
    public function test_public_subscribe_forbidden_when_pack_inactive(): void
    {
        $this->postJson('/api/v1/public/newsletter/subscribe', [
            'email' => 'reader@example.com',
        ])->assertForbidden();
    }

    public function test_public_subscribe_ok_when_pack_active(): void
    {
        $this->activatePack('newsletter');
        Mail::fake();

        $this->postJson('/api/v1/public/newsletter/subscribe', [
            'email' => 'reader@example.com',
            'name' => 'Reader',
        ])->assertCreated();
    }
}
