<?php

declare(strict_types=1);

namespace Modules\Core\System\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Services\BroadcastNotificationService;

class SendBroadcastNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @param  array<string, mixed>  $payload */
    public function __construct(public array $payload) {}

    public function handle(BroadcastNotificationService $broadcasts): void
    {
        try {
            $broadcasts->deliver($this->payload);
        } catch (\Throwable $e) {
            Log::warning('SendBroadcastNotification failed: '.$e->getMessage(), [
                'payload' => $this->payload,
            ]);
        }
    }
}
