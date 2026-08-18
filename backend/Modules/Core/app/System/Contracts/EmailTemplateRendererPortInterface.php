<?php

declare(strict_types=1);

namespace Modules\Core\System\Contracts;

interface EmailTemplateRendererPortInterface
{
    /**
     * @param  array<string, mixed>  $variables
     * @return array{subject: string, body: string, format: string}|null
     */
    public function renderBySlug(string $slug, array $variables = []): ?array;
}
