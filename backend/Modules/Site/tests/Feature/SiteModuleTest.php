<?php

declare(strict_types=1);

namespace Modules\Site\Tests\Feature;

use Tests\TestCase;

class SiteModuleTest extends TestCase
{
    public function test_public_site_route_is_registered(): void
    {
        $response = $this->get('/site');
        $response->assertOk();
        $this->assertTrue(
            str_contains((string) $response->getContent(), 'public')
            || str_contains((string) $response->headers->get('content-type', ''), 'json')
            || str_contains((string) $response->headers->get('content-type', ''), 'html'),
        );
    }

    public function test_site_manifest_declares_layout_and_publishing_deps(): void
    {
        $path = dirname(__DIR__, 2).'/manifest.json';
        $this->assertFileExists($path);
        $raw = file_get_contents($path);
        $this->assertIsString($raw);
        $manifest = json_decode($raw, true);
        $this->assertIsArray($manifest);
        $this->assertSame('site', $manifest['slug']);
        $this->assertSame('audience', $manifest['family']);
        $this->assertArrayHasKey('layout', $manifest['dependencies']);
        $this->assertArrayHasKey('publishing', $manifest['dependencies']);
        $this->assertArrayHasKey('member', $manifest['suggests']);
    }
}
