<?php

declare(strict_types=1);

namespace Modules\Core\System\Tests\Feature;

use Tests\TestCase;

class LayoutManifestContributionTest extends TestCase
{
    public function test_layout_manifest_declares_widget_contribution_points(): void
    {
        $path = base_path('Modules/Layout/manifest.json');
        $this->assertFileExists($path);
        $raw = file_get_contents($path);
        $this->assertIsString($raw);
        $manifest = json_decode($raw, true);
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('widgets', $manifest['contribution_points'] ?? []);
        $this->assertNotEmpty($manifest['contribution_points']['widgets']);
        $types = array_column($manifest['contribution_points']['widgets'], 'type');
        $this->assertContains('html', $types);
        $this->assertContains('recent_posts', $types);
    }
}
