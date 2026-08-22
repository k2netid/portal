<?php

declare(strict_types=1);

namespace Modules\Mail\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Modules\Core\System\Contracts\OutboundMailPortInterface;
use Modules\Mail\Events\MailMessageQueued;
use Modules\Mail\Events\MailMessageSent;
use Modules\Mail\Jobs\SendOutboundMailJob;
use Modules\Mail\Tests\Support\ActivatesMailExtension;
use Tests\TestCase;

class OutboundMailPortTest extends TestCase
{
    use ActivatesMailExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissionsAndRoles();
        $this->activateMailExtension();
    }

    public function test_port_sends_synchronously_and_dispatches_sent_event(): void
    {
        Mail::fake();
        Event::fake([MailMessageSent::class]);

        $user = $this->createSuperAdminUser();

        /** @var OutboundMailPortInterface $port */
        $port = app(OutboundMailPortInterface::class);

        $result = $port->send(
            'recipient@example.com',
            'Hello from port',
            '<p>Body</p>',
            asUser: $user,
            queue: false,
        );

        $this->assertSame('sent', $result['status']);
        $this->assertArrayHasKey('from_address', $result);
        $this->assertNotSame('', $result['from_address']);
        Event::assertDispatched(MailMessageSent::class, function (MailMessageSent $event) use ($user): bool {
            return $event->to === 'recipient@example.com'
                && $event->subject === 'Hello from port'
                && $event->userId === $user->id;
        });
    }

    public function test_port_can_queue_outbound_mail(): void
    {
        Queue::fake();
        Event::fake([MailMessageQueued::class]);

        $user = $this->createSuperAdminUser();

        /** @var OutboundMailPortInterface $port */
        $port = app(OutboundMailPortInterface::class);

        $result = $port->send(
            'queued@example.com',
            'Queued subject',
            '<p>Later</p>',
            asUser: $user,
            queue: true,
        );

        $this->assertSame('queued', $result['status']);
        Queue::assertPushed(SendOutboundMailJob::class, function (SendOutboundMailJob $job) use ($user): bool {
            return $job->to === 'queued@example.com'
                && $job->subject === 'Queued subject'
                && $job->userId === $user->id;
        });
        Event::assertDispatched(MailMessageQueued::class);
    }
}
