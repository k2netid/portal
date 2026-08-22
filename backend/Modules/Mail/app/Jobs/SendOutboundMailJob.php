<?php

declare(strict_types=1);

namespace Modules\Mail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\User;
use Modules\Mail\Models\MailAccount;
use Modules\Mail\Services\MailDispatchService;

class SendOutboundMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 600];

    /**
     * @param  array<int, string>  $cc
     * @param  array<int, string>  $bcc
     * @param  list<array<string, mixed>>  $attachments
     */
    public function __construct(
        public string $to,
        public string $subject,
        public string $htmlBody,
        public array $cc = [],
        public array $bcc = [],
        public ?string $userId = null,
        public ?string $accountId = null,
        public array $attachments = [],
    ) {}

    public function handle(MailDispatchService $mailDispatch): void
    {
        $user = null;
        if (is_string($this->userId) && $this->userId !== '') {
            $user = User::query()->find($this->userId);
        }

        $account = null;
        if (is_string($this->accountId) && $this->accountId !== '') {
            $account = MailAccount::query()->find($this->accountId);
        }

        try {
            $mailDispatch->sendOutbound(
                $this->to,
                $this->subject,
                $this->htmlBody,
                $this->cc,
                $this->bcc,
                $user,
                $account,
                $this->attachments,
            );
        } catch (\Throwable $e) {
            Log::error('Queued outbound mail failed', [
                'to' => $this->to,
                'account_id' => $this->accountId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
