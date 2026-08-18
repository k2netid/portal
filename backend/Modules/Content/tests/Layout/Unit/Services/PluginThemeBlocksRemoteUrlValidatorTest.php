<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Tests\Unit\Services;

use Illuminate\Support\Facades\Config;
use Modules\Content\Layout\Services\PluginThemeBlocksRemoteUrlValidator;
use Tests\TestCase;

class PluginThemeBlocksRemoteUrlValidatorTest extends TestCase
{
    public function test_rejects_when_feature_disabled(): void
    {
        Config::set('layout.remote_plugin_blocks.enabled', false);
        $v = new PluginThemeBlocksRemoteUrlValidator;

        $this->assertNull($v->validate('https://cdn.example.com/plugins/foo/blocks.js'));
    }

    public function test_accepts_https_host_on_allowlist_with_js_path(): void
    {
        Config::set('layout.remote_plugin_blocks.enabled', true);
        Config::set('layout.remote_plugin_blocks.allowed_hosts', ['cdn.example.com']);
        $v = new PluginThemeBlocksRemoteUrlValidator;

        $this->assertSame(
            'https://cdn.example.com/plugins/foo/blocks.js',
            $v->validate('https://cdn.example.com/plugins/foo/blocks.js'),
        );
    }

    public function test_rejects_http_and_unknown_host(): void
    {
        Config::set('layout.remote_plugin_blocks.enabled', true);
        Config::set('layout.remote_plugin_blocks.allowed_hosts', ['cdn.example.com']);
        $v = new PluginThemeBlocksRemoteUrlValidator;

        $this->assertNull($v->validate('http://cdn.example.com/blocks.js'));
        $this->assertNull($v->validate('https://evil.example/blocks.js'));
        $this->assertNull($v->validate('https://cdn.example.com/blocks.css'));
    }
}
