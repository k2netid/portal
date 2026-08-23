<?php

declare(strict_types=1);

namespace Modules\Newsletter\Services;

use Modules\Publishing\Contracts\NewsletterSubscriberCountPortInterface;
use Modules\Newsletter\Models\NewsletterSubscriber;

class NewsletterSubscriberCountPortAdapter implements NewsletterSubscriberCountPortInterface
{
    public function subscribedCount(): int
    {
        return NewsletterSubscriber::query()->where('status', 'subscribed')->count();
    }
}
