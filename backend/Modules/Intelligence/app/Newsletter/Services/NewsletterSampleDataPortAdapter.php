<?php

declare(strict_types=1);

namespace Modules\Intelligence\Newsletter\Services;

use Modules\Content\Publishing\Contracts\NewsletterSampleDataPortInterface;
use Modules\Intelligence\Newsletter\Models\NewsletterSubscriber;

class NewsletterSampleDataPortAdapter implements NewsletterSampleDataPortInterface
{
    public function upsertSubscriberByEmail(string $email, array $attributes): void
    {
        NewsletterSubscriber::query()->updateOrCreate(
            ['email' => $email],
            $attributes,
        );
    }
}
