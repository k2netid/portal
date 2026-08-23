<?php

declare(strict_types=1);

namespace Modules\Publishing\Contracts;

/**
 * Allows Content tier seeders to touch newsletter data without importing Intelligence models.
 */
interface NewsletterSampleDataPortInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function upsertSubscriberByEmail(string $email, array $attributes): void;
}
