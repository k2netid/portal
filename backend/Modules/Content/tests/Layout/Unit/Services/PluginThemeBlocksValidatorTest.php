<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Unit\Services;

use Modules\Content\Layout\Services\PluginThemeBlocksValidator;
use Tests\TestCase;

class PluginThemeBlocksValidatorTest extends TestCase
{
    public function test_rejects_unknown_slot(): void
    {
        $validator = new PluginThemeBlocksValidator;
        $errors = $validator->validateSettings([
            'theme_blocks' => [['slot' => 'invalid_slot_xyz']],
        ]);

        $this->assertArrayHasKey('theme_blocks.0', $errors);
    }

    public function test_normalize_strips_invalid_slots(): void
    {
        $validator = new PluginThemeBlocksValidator;
        $out = $validator->normalizeSettings([
            'theme_blocks' => [['slot' => 'after_post_content'], ['slot' => 'nope']],
            'other' => true,
        ]);

        $this->assertSame([['slot' => 'after_post_content']], $out['theme_blocks']);
        $this->assertTrue($out['other']);
    }
}
