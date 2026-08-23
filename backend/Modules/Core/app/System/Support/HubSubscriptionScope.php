<?php

declare(strict_types=1);

namespace Modules\Core\System\Support;

use Illuminate\Support\Facades\Context;

/** Active platform subscription for the current HTTP request (hub control plane). */
final class HubSubscriptionScope
{
    public const KEY_SUBSCRIPTION_ID = 'subscription_id';

    public const KEY_SUBSCRIPTION_DOMAIN = 'subscription_domain';

    public static function set(string $subscriptionId, ?string $domain = null): void
    {
        Context::add(self::KEY_SUBSCRIPTION_ID, $subscriptionId);
        if ($domain !== null && $domain !== '') {
            Context::add(self::KEY_SUBSCRIPTION_DOMAIN, $domain);
        }
    }

    public static function id(): ?string
    {
        $id = Context::get(self::KEY_SUBSCRIPTION_ID);

        return is_string($id) && $id !== '' ? $id : null;
    }

    public static function domain(): ?string
    {
        $domain = Context::get(self::KEY_SUBSCRIPTION_DOMAIN);

        return is_string($domain) && $domain !== '' ? $domain : null;
    }

    public static function has(): bool
    {
        return self::id() !== null;
    }
}
