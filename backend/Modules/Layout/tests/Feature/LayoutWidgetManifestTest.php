<?php

declare(strict_types=1);

namespace Modules\Layout\Tests\Feature;

use Modules\Core\System\Contracts\LayoutRegistryInterface;
use Tests\TestCase;

class LayoutWidgetManifestTest extends TestCase
{
    public function test_registered_widget_types_match_manifest(): void
    {
        $path = base_path('Modules/Layout/manifest.json');
        $this->assertFileExists($path);

        $manifest = json_decode((string) file_get_contents($path), true);
        $this->assertIsArray($manifest);
        $declared = array_values(array_map(
            static fn (array $widget): string => $widget['type'],
            $manifest['contribution_points']['widgets'] ?? [],
        ));

        $registered = array_keys(app(LayoutRegistryInterface::class)->getWidgetTypes('publishing'));

        $this->assertSame(8, count($declared));
        $this->assertEqualsCanonicalizing($declared, $registered);
    }
}
