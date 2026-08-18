<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Modules\Content\Layout\Services\ThemePackageInstallService;
use Modules\Content\Layout\Services\ThemeService;
use Tests\TestCase;
use ZipArchive;

class ThemePackageInstallServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_from_zip_registers_uploaded_theme(): void
    {
        Config::set('layout.uploaded_themes.enabled', true);

        $zipPath = $this->makeThemeZip('test-upload-theme', [
            'name' => 'Test Upload',
            'slug' => 'test-upload-theme',
            'type' => 'frontend',
        ]);

        $file = new UploadedFile($zipPath, 'theme.zip', 'application/zip', null, true);
        $service = new ThemePackageInstallService(new ThemeService);

        $result = $service->installFromZip($file);
        $theme = $result['theme'];

        $this->assertSame('test-upload-theme', $theme->slug);
        $this->assertSame('uploaded', $theme->source);
        $this->assertDirectoryExists(storage_path('app/public/themes/test-upload-theme'));

        File::deleteDirectory(storage_path('app/public/themes/test-upload-theme'));
        @unlink($zipPath);
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    private function makeThemeZip(string $slug, array $manifest): string
    {
        $dir = storage_path('app/temp/test-zip-'.$slug);
        File::deleteDirectory($dir);
        File::ensureDirectoryExists($dir);
        File::put($dir.'/theme.json', json_encode($manifest, JSON_THROW_ON_ERROR));

        $zipPath = storage_path('app/temp/'.$slug.'.zip');
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFile($dir.'/theme.json', 'theme.json');
        $zip->close();
        File::deleteDirectory($dir);

        return $zipPath;
    }
}
