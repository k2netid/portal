<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Services;

use JsonException;
use Modules\Intelligence\Ai\Contracts\AiProviderInterface;
use Modules\Intelligence\Ai\Services\Exceptions\PublishingDraftParseException;

class PublishingContentDraftService
{
    public function __construct(
        private readonly ?AiProviderInterface $provider = null,
    ) {}

    /**
     * @param  array{
     *   topic: string,
     *   content_type?: string,
     *   category_name?: string|null,
     *   tags?: array<int, string>,
     *   tone?: string|null,
     *   provider?: string|null,
     *   model?: string|null
     * }  $input
     * @return array{title: string, excerpt: string, intro: string, body: string, provider: string}
     */
    public function draft(array $input): array
    {
        $topic = $input['topic'];
        $contentType = $input['content_type'] ?? 'post';
        $categoryName = $input['category_name'] ?? '';
        $tags = $input['tags'] ?? [];
        $tone = $input['tone'] ?? 'professional';
        $provider = $input['provider'] ?? null;
        $model = $input['model'] ?? '';

        $tagLine = $tags === [] ? '' : 'Tags: '.implode(', ', $tags).'.';
        $context = trim("Content type: {$contentType}. Category: {$categoryName}. {$tagLine}");

        $prompt = <<<PROMPT
Write a Jejakawan {$contentType} draft in {$tone} tone about: {$topic}.
Respond with JSON only (no markdown fences) using exactly these keys:
{"title":"...","excerpt":"...","intro":"...","body":"..."}
- excerpt: max 160 characters
- intro: 1-2 short paragraphs (plain text)
- body: HTML with <p> tags only
PROMPT;

        $service = $this->provider ?? AiProviderFactory::make(is_string($provider) ? $provider : null);
        $raw = $service->generateText($prompt, $context, is_string($model) ? $model : '');

        $parsed = $this->parseDraftJson($raw);

        return [
            'title' => $parsed['title'],
            'excerpt' => $parsed['excerpt'],
            'intro' => $parsed['intro'],
            'body' => $parsed['body'],
            'provider' => $service->getName(),
        ];
    }

    /**
     * @return array{title: string, excerpt: string, intro: string, body: string}
     */
    private function parseDraftJson(string $raw): array
    {
        $trimmed = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $trimmed, $matches) === 1) {
            $trimmed = trim($matches[1]);
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new PublishingDraftParseException('AI response was not valid JSON: '.$e->getMessage());
        }

        if (! is_array($decoded)) {
            throw new PublishingDraftParseException('AI response must be a JSON object');
        }

        $payload = [];
        foreach ($decoded as $key => $value) {
            if (is_string($key)) {
                $payload[$key] = $value;
            }
        }

        $title = $this->stringField($payload, 'title');
        $excerpt = $this->stringField($payload, 'excerpt');
        $intro = $this->stringField($payload, 'intro');
        $body = $this->stringField($payload, 'body');

        if ($title === '' || $body === '') {
            throw new PublishingDraftParseException('AI draft must include title and body');
        }

        return [
            'title' => $title,
            'excerpt' => $excerpt,
            'intro' => $intro,
            'body' => $body,
        ];
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private function stringField(array $decoded, string $key): string
    {
        $value = $decoded[$key] ?? '';

        return is_scalar($value) ? trim((string) $value) : '';
    }
}
