<?php

declare(strict_types=1);

namespace Modules\Mail\Services;

use Illuminate\Support\Facades\Event;
use Modules\Core\System\Contracts\OutboundMailPortInterface;
use Modules\Core\System\Facades\Hook;
use Modules\Core\System\Models\User;
use Modules\Mail\Events\MailMessageQueued;
use Modules\Mail\Jobs\SendOutboundMailJob;
use Modules\Mail\Models\MailAccount;

class OutboundMailDispatcher implements OutboundMailPortInterface
{
    public function __construct(
        protected MailDispatchService $mailDispatch,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        array $cc = [],
        array $bcc = [],
        ?User $asUser = null,
        ?string $accountId = null,
        array $attachments = [],
        ?bool $queue = null,
    ): array {
        $shouldQueue = $queue ?? (bool) config('mail_module.queue_outbound', false);

        $account = null;
        if (is_string($accountId) && $accountId !== '') {
            $accountQuery = MailAccount::query()->whereKey($accountId);
            if ($asUser instanceof User) {
                $accountQuery->where('user_id', $asUser->id);
            }
            $account = $accountQuery->first();
        }

        if ($shouldQueue) {
            SendOutboundMailJob::dispatch(
                $to,
                $subject,
                $htmlBody,
                $cc,
                $bcc,
                $asUser?->id,
                $account !== null ? $account->id : $accountId,
                $attachments,
            );

            Event::dispatch(new MailMessageQueued(
                $to,
                $subject,
                $account !== null ? $account->id : $accountId,
                $asUser?->id,
                $cc,
                $bcc,
            ));

            Hook::action('mail.message_queued', $to, $subject, $asUser);

            return ['status' => 'queued'];
        }

        $result = $this->mailDispatch->sendOutbound(
            $to,
            $subject,
            $htmlBody,
            $cc,
            $bcc,
            $asUser,
            $account,
            $attachments,
        );

        return array_merge(['status' => 'sent'], $result);
    }
}
