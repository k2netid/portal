<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Modules\Core\System\Contracts\EmailTemplateRendererPortInterface;
use Modules\Core\System\Models\EmailTemplate;

class EmailTemplateRenderer implements EmailTemplateRendererPortInterface
{
    /**
     * @param  array<string, mixed>  $variables
     * @return array{subject: string, body: string, format: string}|null
     */
    public function renderBySlug(string $slug, array $variables = []): ?array
    {
        $template = EmailTemplate::getBySlug($slug);
        if (! $template instanceof EmailTemplate) {
            return null;
        }

        $rendered = $template->render($variables);
        $subject = is_string($rendered['subject'] ?? null) ? $rendered['subject'] : $template->subject;
        $body = is_string($rendered['body'] ?? null) ? $rendered['body'] : (string) $template->body;
        $format = is_string($template->format ?? null) ? $template->format : 'html';

        return [
            'subject' => $subject,
            'body' => $body,
            'format' => $format,
        ];
    }
}
