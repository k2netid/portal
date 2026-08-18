<?php

declare(strict_types=1);

namespace Modules\Intelligence\Newsletter\Services;

use Modules\Content\Publishing\Contracts\NewsletterSubscriberCountPortInterface;
use Modules\Intelligence\Newsletter\Models\NewsletterSubscriber;

class NewsletterSubscriberCountPortAdapter implements NewsletterSubscriberCountPortInterface
{
    public function subscribedCount(): int
    {
        return NewsletterSubscriber::query()->where('status', 'subscribed')->count();
    }
}
