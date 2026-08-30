<?php

declare(strict_types=1);

namespace Modules\Publishing\Contracts;

/**
 * Dashboard stats for Publishing without importing Intelligence models.
 */
interface NewsletterSubscriberCountPortInterface
{
    public function subscribedCount(): int;
}
