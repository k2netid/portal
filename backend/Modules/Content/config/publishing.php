<?php

declare(strict_types=1);

$profileRaw = config('ja_publishing_env.profile_public_theme_api', false);
$maxAgeRaw = config('ja_publishing_env.public_active_theme_http_cache_max_age', 60);
$themePathRaw = config('ja_publishing_env.theme_views_relative_path', '../frontend/src/modules/Content/Layout/views/themes');

return [
    'name' => 'Publishing',
    'profile_public_theme_api' => is_bool($profileRaw)
        ? $profileRaw
        : (bool) filter_var($profileRaw, FILTER_VALIDATE_BOOLEAN),
    'public_active_theme_http_cache_max_age' => max(0, is_numeric($maxAgeRaw) ? (int) $maxAgeRaw : 60),
    'theme_views_relative_path' => is_string($themePathRaw)
        ? $themePathRaw
        : '../frontend/src/modules/Content/Layout/views/themes',
];
