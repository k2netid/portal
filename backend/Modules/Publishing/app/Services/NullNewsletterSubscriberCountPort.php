<?php

declare(strict_types=1);

namespace Modules\Publishing\Services;

use Modules\Publishing\Contracts\NewsletterSubscriberCountPortInterface;

/** Default until Newsletter pack binds a real adapter. */
class NullNewsletterSubscriberCountPort implements NewsletterSubscriberCountPortInterface
{
    public function subscribedCount(): int
    {
        return 0;
    }
}
