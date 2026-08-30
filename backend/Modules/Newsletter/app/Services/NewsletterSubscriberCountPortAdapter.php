<?php

declare(strict_types=1);

namespace Modules\Newsletter\Services;

use Modules\Newsletter\Models\NewsletterSubscriber;
use Modules\Publishing\Contracts\NewsletterSubscriberCountPortInterface;

class NewsletterSubscriberCountPortAdapter implements NewsletterSubscriberCountPortInterface
{
    public function subscribedCount(): int
    {
        return NewsletterSubscriber::query()->where('status', 'subscribed')->count();
    }
}
