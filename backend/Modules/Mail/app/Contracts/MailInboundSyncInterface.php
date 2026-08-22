<?php

declare(strict_types=1);

namespace Modules\Mail\Contracts;

use Modules\Core\System\Models\User;
use Modules\Mail\Models\MailAccount;

/**
 * Refresh / import hook for the local mailbox index.
 * Kernel default: LocalIndexInboundSync (no remote protocol).
 */
interface MailInboundSyncInterface
{
    /**
     * @return array{mode: string, imported: int, total_messages: int, synced_at: string}
     */
    public function sync(User $user, ?MailAccount $account = null): array;
}
