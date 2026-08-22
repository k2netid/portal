<?php

declare(strict_types=1);

namespace Modules\Mail\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\System\Models\User;
use Modules\Mail\Models\MailAccount;
use Modules\Mail\Models\MailMessage;

class UserMailRepository
{
    public function __construct(
        private readonly User $user,
    ) {}

    public function user(): User
    {
        return $this->user;
    }

    /**
     * @return Builder<MailMessage>
     */
    public function messages(?string $accountId = null): Builder
    {
        $query = MailMessage::query()->where('user_id', $this->user->id);

        if (is_string($accountId) && $accountId !== '') {
            $query->where('account_id', $accountId);
        }

        return $query;
    }

    public function findMessage(string $id): ?MailMessage
    {
        return $this->messages()->find($id);
    }

    public function findMessageOrFail(string $id): MailMessage
    {
        $message = $this->findMessage($id);
        if (! $message instanceof MailMessage) {
            abort(404, 'Message not found');
        }

        return $message;
    }

    public function resolveAccount(?string $accountId = null): ?MailAccount
    {
        if (is_string($accountId) && $accountId !== '') {
            return MailAccount::query()
                ->where('user_id', $this->user->id)
                ->where('id', $accountId)
                ->first();
        }

        return MailAccount::query()
            ->where('user_id', $this->user->id)
            ->where('is_default', true)
            ->first();
    }
}
