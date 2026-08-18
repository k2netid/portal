<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Services;

use JsonException;
use Modules\Intelligence\Ai\Contracts\AiProviderInterface;
use Modules\Intelligence\Ai\Services\Exceptions\TaxonomySuggestParseException;

class PublishingTaxonomySuggestService
{
    public function __construct(
        private readonly ?AiProviderInterface $provider = null,
    ) {}

    /**
     * @param  array{
     *   title: string,
     *   excerpt?: string|null,
     *   body?: string|null,
     *   existing_categories?: array<int, string>,
     *   existing_tags?: array<int, string>,
     *   provider?: string|null,
     *   model?: string|null
     * }  $input
     * @return array{category_name: string, tags: array<int, string>, provider: string}
     */
    public function suggest(array $input): array
    {
        $title = $input['title'];
        $excerpt = $input['excerpt'] ?? '';
        $body = $input['body'] ?? '';
        $categories = $input['existing_categories'] ?? [];
        $tags = $input['existing_tags'] ?? [];
        $provider = $input['provider'] ?? null;
        $model = $input['model'] ?? '';

        $snippet = trim(strip_tags((string) $excerpt.' '.mb_substr((string) $body, 0, 1200)));
        $categoryLine = $categories === [] ? 'none' : implode(', ', $categories);
        $tagLine = $tags === [] ? 'none' : implode(', ', $tags);

        $prompt = <<<PROMPT
Suggest taxonomy for a Jejakawan article.
Title: {$title}
Snippet: {$snippet}
Existing categories (prefer reuse when fitting): {$categoryLine}
Existing tags (prefer reuse when fitting): {$tagLine}
Respond with JSON only (no markdown fences):
{"category_name":"...","tags":["tag1","tag2"]}
Rules: category_name is a single string; tags is 1-5 short lowercase-ish labels.
PROMPT;

        $service = $this->provider ?? AiProviderFactory::make(is_string($provider) ? $provider : null);
        $raw = $service->generateText($prompt, '', is_string($model) ? $model : '');

        $parsed = $this->parseJson($raw);

        return [
            'category_name' => $parsed['category_name'],
            'tags' => $parsed['tags'],
            'provider' => $service->getName(),
        ];
    }

    /**
     * @return array{category_name: string, tags: array<int, string>}
     */
    private function parseJson(string $raw): array
    {
        $trimmed = trim($raw);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $trimmed, $matches) === 1) {
            $trimmed = trim($matches[1]);
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TaxonomySuggestParseException('AI response was not valid JSON: '.$e->getMessage());
        }

        if (! is_array($decoded)) {
            throw new TaxonomySuggestParseException('AI response must be a JSON object');
        }

        $payload = [];
        foreach ($decoded as $key => $value) {
            if (is_string($key)) {
                $payload[$key] = $value;
            }
        }

        $categoryName = $this->stringField($payload, 'category_name');
        $tagsRaw = $payload['tags'] ?? [];
        $tags = [];
        if (is_array($tagsRaw)) {
            foreach ($tagsRaw as $tag) {
                if (is_scalar($tag)) {
                    $normalized = trim((string) $tag);
                    if ($normalized !== '') {
                        $tags[] = $normalized;
                    }
                }
            }
        }

        if ($categoryName === '' && $tags === []) {
            throw new TaxonomySuggestParseException('AI taxonomy suggestion was empty');
        }

        return [
            'category_name' => $categoryName,
            'tags' => array_values(array_unique($tags)),
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
