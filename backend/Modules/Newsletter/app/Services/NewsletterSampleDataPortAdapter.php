<?php

declare(strict_types=1);

namespace Modules\Newsletter\Services;

use Modules\Newsletter\Models\NewsletterSubscriber;
use Modules\Publishing\Contracts\NewsletterSampleDataPortInterface;

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
