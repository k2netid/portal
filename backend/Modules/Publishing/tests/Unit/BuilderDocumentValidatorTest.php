<?php

declare(strict_types=1);

namespace Modules\Publishing\Tests\Unit;

use Modules\Publishing\Support\BuilderDocumentValidator;
use PHPUnit\Framework\TestCase;

class BuilderDocumentValidatorTest extends TestCase
{
    public function test_empty_meta_without_builder_keys_is_ok(): void
    {
        $this->assertSame([], (new BuilderDocumentValidator)->validate(['seo_title' => 'x']));
    }

    public function test_valid_tree_passes(): void
    {
        $errors = (new BuilderDocumentValidator)->validate([
            'builder_schema_version' => 1,
            'builder_blocks' => [
                [
                    'id' => 'module-1',
                    'type' => 'section',
                    'settings' => [],
                    'children' => [
                        [
                            'id' => 'module-2',
                            'type' => 'heading',
                            'settings' => ['text' => 'Hello'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame([], $errors);
    }

    public function test_rejects_invalid_type_and_non_list_children(): void
    {
        $errors = (new BuilderDocumentValidator)->validate([
            'builder_blocks' => [
                [
                    'id' => 'x',
                    'type' => 'Heading Block',
                    'children' => 'bad',
                ],
            ],
        ]);

        $this->assertArrayHasKey('meta.builder_blocks.0.type', $errors);
        $this->assertArrayHasKey('meta.builder_blocks.0.children', $errors);
    }

    public function test_rejects_unsupported_schema_version(): void
    {
        $errors = (new BuilderDocumentValidator)->validate([
            'builder_schema_version' => 9,
            'builder_blocks' => [],
        ]);

        $this->assertArrayHasKey('meta.builder_schema_version', $errors);
    }
}
