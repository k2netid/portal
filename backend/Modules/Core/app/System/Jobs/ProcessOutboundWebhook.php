<?php

namespace Modules\Core\System\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Webhook;
use Modules\Core\System\Models\WebhookDelivery;

class ProcessOutboundWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300, 3600, 86400];

    protected Webhook $webhook;

    protected string $event;

    /** @var array<string, mixed> */
    protected array $payload;

    /** @param array<string, mixed> $payload */
    public function __construct(Webhook $webhook, string $event, array $payload)
    {
        $this->webhook = $webhook;
        $this->event = $event;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        if (! $this->webhook->is_active) {
            return;
        }

        $attempt = $this->attempts();
        $started = hrtime(true);
        $signature = hash_hmac('sha256', json_encode($this->payload) ?: '', $this->webhook->secret ?? '');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Jejakawan-Event' => $this->event,
                    'X-Jejakawan-Signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->webhook->url, $this->payload);

            $durationMs = (int) ((hrtime(true) - $started) / 1_000_000);

            if ($response->successful()) {
                $this->recordDelivery('success', $response->status(), $this->truncate($response->body()), null, $attempt, $durationMs);

                return;
            }

            $this->recordDelivery(
                'failed',
                $response->status(),
                $this->truncate($response->body()),
                'HTTP '.$response->status(),
                $attempt,
                $durationMs
            );

            Log::warning("Webhook delivery failed for {$this->webhook->name}", [
                'url' => $this->webhook->url,
                'event' => $this->event,
                'status' => $response->status(),
            ]);

            $this->release($this->backoff[$attempt - 1] ?? 3600);
        } catch (\Exception $e) {
            $durationMs = (int) ((hrtime(true) - $started) / 1_000_000);
            $this->recordDelivery('failed', null, null, $e->getMessage(), $attempt, $durationMs);

            Log::error("Webhook delivery exception for {$this->webhook->name}", [
                'url' => $this->webhook->url,
                'event' => $this->event,
                'error' => $e->getMessage(),
            ]);

            $this->release($this->backoff[$attempt - 1] ?? 3600);
        }
    }

    private function recordDelivery(
        string $status,
        ?int $statusCode,
        ?string $responseBody,
        ?string $errorMessage,
        int $attempt,
        int $durationMs
    ): void {
        WebhookDelivery::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'attempt' => $attempt,
            'status_code' => $statusCode,
            'status' => $status,
            'response_body' => $responseBody,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
        ]);
    }

    private function truncate(?string $body, int $max = 4000): ?string
    {
        if ($body === null) {
            return null;
        }

        return strlen($body) > $max ? substr($body, 0, $max).'…' : $body;
    }
}
