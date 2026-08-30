<?php

declare(strict_types=1);

namespace Modules\Layout\Services;

final class PluginThemeBlocksRemoteUrlValidator
{
    public function isFeatureEnabled(): bool
    {
        return (bool) config('layout.remote_plugin_blocks.enabled', false);
    }

    /**
     * @return list<string>
     */
    public function allowedHosts(): array
    {
        $hosts = config('layout.remote_plugin_blocks.allowed_hosts', []);

        return is_array($hosts) ? array_values(array_filter($hosts, static fn (mixed $h): bool => is_string($h) && $h !== '')) : [];
    }

    public function validate(?string $url): ?string
    {
        if (! $this->isFeatureEnabled() || $url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return null;
        }

        if (($parts['scheme'] ?? '') !== 'https') {
            return null;
        }

        $host = $parts['host'] ?? '';
        if ($host === '' || ! in_array($host, $this->allowedHosts(), true)) {
            return null;
        }

        $path = $parts['path'] ?? '';
        if ($path === '' || ! str_ends_with(strtolower($path), '.js')) {
            return null;
        }

        return $url;
    }
}
