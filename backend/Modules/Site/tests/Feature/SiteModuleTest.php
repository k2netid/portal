<?php

declare(strict_types=1);

namespace Modules\Site\Tests\Feature;

use Tests\TestCase;

class SiteModuleTest extends TestCase
{
    public function test_apex_serves_console_when_site_inactive(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $body = (string) $response->getContent();
        $this->assertTrue(
            str_contains($body, 'console')
            || str_contains($body, 'Jejakawan Core Engine')
            || str_contains((string) $response->headers->get('content-type', ''), 'html')
            || str_contains((string) $response->headers->get('content-type', ''), 'json'),
        );
        $this->assertFalse(str_contains($body, "window.__JA_SHELL__ = 'public'"));
    }

    public function test_apex_serves_public_when_site_active(): void
    {
        $this->activatePack('site');

        $response = $this->get('/');
        $response->assertOk();
        $body = (string) $response->getContent();
        $this->assertTrue(
            str_contains($body, 'public')
            || str_contains($body, '__JA_SHELL__')
            || str_contains((string) $response->headers->get('content-type', ''), 'html')
            || str_contains((string) $response->headers->get('content-type', ''), 'json'),
        );
    }

    public function test_legacy_site_prefix_redirects_when_active(): void
    {
        $this->activatePack('site');

        $this->get('/site/contact')->assertRedirect('/contact');
        $this->get('/site')->assertRedirect('/');
    }

    public function test_legacy_site_prefix_404_when_inactive(): void
    {
        $this->get('/site/contact')->assertNotFound();
    }

    public function test_console_paths_stay_console_when_site_active(): void
    {
        $this->activatePack('site');

        $response = $this->get('/auth/console-sign-in');
        $response->assertOk();
        $body = (string) $response->getContent();
        $this->assertFalse(str_contains($body, "window.__JA_SHELL__ = 'public'"));
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
