<?php

declare(strict_types=1);

namespace App\Support;

use Modules\Core\System\Models\Setting;

final class SpaHtmlFavicon
{
    public static function resolveHref(string $shell): string
    {
        $app = self::trimString(Setting::get('app_favicon', ''));
        $site = self::trimString(Setting::get('site_favicon', ''));
        $theme = $shell === 'public' ? self::activeThemeBrandFavicon() : '';

        $preferred = $shell === 'public'
            ? [$theme, $app, $site]
            : [$app, $site, $theme];

        foreach ($preferred as $href) {
            if ($href !== '' && ! self::isGenericEngineIcon($href)) {
                return $href;
            }
        }

        foreach ($preferred as $href) {
            if ($href !== '') {
                return $href;
            }
        }

        return '/favicon.ico';
    }

    public static function inject(string $html, string $href): string
    {
        $safe = htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $tag = '<link rel="icon" href="'.$safe.'">';
        $count = 0;
        $replaced = preg_replace(
            '/<link\s[^>]*rel=["\'](?:shortcut\s+)?icon["\'][^>]*>/i',
            $tag,
            $html,
            -1,
            $count,
        );

        if (is_string($replaced) && $count > 0) {
            return $replaced;
        }

        $inserted = preg_replace('/<\/head>/i', $tag."\n</head>", $html, 1);

        return is_string($inserted) ? $inserted : $html;
    }

    public static function injectForShell(string $html, string $shell): string
    {
        return self::inject($html, self::resolveHref($shell));
    }

    public static function isGenericEngineIcon(string $href): bool
    {
        $path = strtolower(parse_url($href, PHP_URL_PATH) ?: $href);

        return str_ends_with($path, '/favicon.ico') || $path === 'favicon.ico';
    }

    private static function trimString(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }
        if (is_array($value) && isset($value['url']) && is_string($value['url'])) {
            return trim($value['url']);
        }

        return '';
    }

    private static function activeThemeBrandFavicon(): string
    {
        if (! class_exists(\Modules\Layout\Models\Theme::class)) {
            return '';
        }

        try {
            $theme = \Modules\Layout\Models\Theme::query()
                ->where('is_active', true)
                ->where('type', 'frontend')
                ->where('status', 'active')
                ->first();
        } catch (\Throwable) {
            return '';
        }

        if ($theme === null) {
            return '';
        }

        return self::trimString($theme->getSetting('brand_favicon', ''));
    }
}
