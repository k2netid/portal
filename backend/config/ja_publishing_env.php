<?php

declare(strict_types=1);

/**
 * Publishing-related env-backed defaults (must live under /config for config:cache).
 */
return [
    'profile_public_theme_api' => filter_var(env('PROFILE_PUBLIC_THEME_API', false), FILTER_VALIDATE_BOOL),
    'public_active_theme_http_cache_max_age' => max(0, (int) env('PUBLISHING_PUBLIC_ACTIVE_THEME_HTTP_CACHE_MAX_AGE', 60)),
    'theme_views_relative_path' => env('PUBLISHING_THEME_VIEWS_RELATIVE_PATH', '../frontend/src/modules/Layout/views/themes'),
];
