<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Unit\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Modules\Content\Layout\Support\ThemeViews;
use Tests\TestCase;

class ThemeViewsTest extends TestCase
{
    public function test_resolves_configured_relative_path_when_directory_exists(): void
    {
        $dir = 'resources/js/themes_test_configured';
        $absolute = base_path($dir);
        File::ensureDirectoryExists($absolute);

        Config::set('publishing.theme_views_relative_path', $dir);

        $this->assertSame($dir, ThemeViews::relativePathFromBackendRoot());
        $this->assertTrue(ThemeViews::diagnostics()['exists']);

        File::deleteDirectory($absolute);
    }

    public function test_diagnostics_returns_expected_keys(): void
    {
        $diag = ThemeViews::diagnostics();

        $this->assertArrayHasKey('relative', $diag);
        $this->assertArrayHasKey('absolute', $diag);
        $this->assertArrayHasKey('exists', $diag);
        $this->assertArrayHasKey('environment', $diag);
    }
}
