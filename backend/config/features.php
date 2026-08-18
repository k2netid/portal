<?php

declare(strict_types=1);

return [
    'uploaded_themes' => filter_var(env('FEATURE_UPLOADED_THEMES', false), FILTER_VALIDATE_BOOL),
    'theme_upload_max_bytes' => (int) env('THEME_UPLOAD_MAX_BYTES', 52_428_800),
    'theme_csp_script_extra' => array_values(array_filter(array_map('trim', explode(',', (string) env('THEME_CSP_SCRIPT_EXTRA', ''))))),
    'force_https_urls' => env('FORCE_HTTPS_URLS'),
    'remote_plugin_blocks' => filter_var(env('FEATURE_REMOTE_PLUGIN_BLOCKS', false), FILTER_VALIDATE_BOOL),
    'remote_plugin_blocks_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env('THEME_REMOTE_BLOCK_HOSTS', ''))))),
    'remote_plugin_blocks_csp_hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => 'https://'.$host,
        array_values(array_filter(array_map('trim', explode(',', (string) env('THEME_REMOTE_BLOCK_HOSTS', ''))))),
    ))),
];
