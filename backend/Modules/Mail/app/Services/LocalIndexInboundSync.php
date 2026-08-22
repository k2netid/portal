<?php

declare(strict_types=1);

namespace Modules\Mail\Services;

use Modules\Core\System\Models\User;
use Modules\Mail\Contracts\MailInboundSyncInterface;
use Modules\Mail\Models\MailAccount;

/**
 * Kernel inbound sync — refreshes local index counts only (no IMAP).
 */
class LocalIndexInboundSync implements MailInboundSyncInterface
{
    public function sync(User $user, ?MailAccount $account = null): array
    {
        $repo = new UserMailRepository($user);
        $query = $repo->messages();
        if ($account instanceof MailAccount) {
            $query->where('account_id', $account->id);
        }

        return [
            'mode' => 'local_index',
            'imported' => 0,
            'total_messages' => $query->count(),
            'synced_at' => now()->toIso8601String(),
        ];
    }
}
