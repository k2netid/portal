<?php

declare(strict_types=1);

namespace Modules\CmsAi\Tests\Unit;

use Modules\CmsAi\Contracts\AiProviderInterface;
use Modules\CmsAi\Services\PublishingContentDraftService;
use Tests\TestCase;

class PublishingContentDraftServiceTest extends TestCase
{
    public function test_draft_parses_json_from_provider(): void
    {
        $service = new PublishingContentDraftService(new FakeDraftAiProvider);

        $result = $service->draft([
            'topic' => 'organization enrollment tips',
            'content_type' => 'post',
            'category_name' => 'News',
            'tags' => ['education'],
        ]);

        $this->assertSame('Sample Title', $result['title']);
        $this->assertSame('<p>Body paragraph</p>', $result['body']);
        $this->assertSame('Fake AI', $result['provider']);
    }
}

final class FakeDraftAiProvider implements AiProviderInterface
{
    public function getName(): string
    {
        return 'Fake AI';
    }

    public function generateText(string $prompt, string $context = '', string $model = ''): string
    {
        return json_encode([
            'title' => 'Sample Title',
            'excerpt' => 'Short excerpt',
            'intro' => 'Intro paragraph',
            'body' => '<p>Body paragraph</p>',
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
