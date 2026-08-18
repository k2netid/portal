<?php

declare(strict_types=1);

return [
    'name' => 'Layout',

    /** Default slot names exposed to the public theme (App Blocks). */
    'plugin_slot_definitions' => require __DIR__.'/plugin_slot_definitions.php',

    'plugin_theme_slots' => [
        'after_header',
        'before_footer',
        'after_post_content',
    ],

    /**
     * Bundled plugin slug => default slots (overridable via plugin settings.theme_blocks).
     *
     * @var array<string, list<string>>
     */
    'plugin_theme_blocks' => require __DIR__.'/plugin_theme_blocks.php',

    'uploaded_themes_enabled' => (bool) config('features.uploaded_themes'),

    'uploaded_themes' => [
        'enabled' => (bool) config('features.uploaded_themes'),
        'max_zip_bytes' => config('features.theme_upload_max_bytes'),
        'csp_script_extra' => config('features.theme_csp_script_extra'),
    ],

    'remote_plugin_blocks' => [
        'enabled' => (bool) config('features.remote_plugin_blocks'),
        'allowed_hosts' => config('features.remote_plugin_blocks_hosts'),
        'csp_script_hosts' => config('features.remote_plugin_blocks_csp_hosts'),
    ],
];
