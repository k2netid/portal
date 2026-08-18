<?php

declare(strict_types=1);

namespace Modules\Intelligence\Ai\Tests\Unit;

use Modules\Intelligence\Ai\Contracts\AiProviderInterface;
use Modules\Intelligence\Ai\Services\PublishingTaxonomySuggestService;
use Tests\TestCase;

class PublishingTaxonomySuggestServiceTest extends TestCase
{
    public function test_suggest_parses_json_from_provider(): void
    {
        $service = new PublishingTaxonomySuggestService(new FakeTaxonomyAiProvider);

        $result = $service->suggest([
            'title' => 'New semester programs',
            'excerpt' => 'Overview of courses',
            'existing_categories' => ['News', 'Academics'],
            'existing_tags' => ['organization'],
        ]);

        $this->assertSame('Academics', $result['category_name']);
        $this->assertSame(['enrollment', 'courses'], $result['tags']);
        $this->assertSame('Fake AI', $result['provider']);
    }
}

final class FakeTaxonomyAiProvider implements AiProviderInterface
{
    public function getName(): string
    {
        return 'Fake AI';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        return json_encode([
            'category_name' => 'Academics',
            'tags' => ['enrollment', 'courses'],
        ], JSON_THROW_ON_ERROR);
    }

    public function getModels(): array
    {
        return [];
    }

    public function testConnection(): bool
    {
        return true;
    }
}
