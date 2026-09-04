<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed Plugin Theme Slot IDs
    |--------------------------------------------------------------------------
    |
    | These slots are recognized across all public frontend themes for plugin
    | app blocks injection.
    |
    */
    'plugin_theme_slots' => [
        'after_header',
        'after_hero',
        'before_footer',
        'after_post_content',
        'sidebar_article',
    ],

    /*
    |--------------------------------------------------------------------------
    | Slot Definitions & Metadata
    |--------------------------------------------------------------------------
    */
    'plugin_slot_definitions' => [
        'after_header' => [
            'label' => 'Below Site Header',
            'maxBlocks' => 5,
        ],
        'after_hero' => [
            'label' => 'Below Hero Section',
            'maxBlocks' => 5,
        ],
        'before_footer' => [
            'label' => 'Above Site Footer',
            'maxBlocks' => 5,
        ],
        'after_post_content' => [
            'label' => 'After Article Body',
            'maxBlocks' => 5,
        ],
        'sidebar_article' => [
            'label' => 'Article Sidebar',
            'maxBlocks' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Plugin Theme Blocks
    |--------------------------------------------------------------------------
    */
    'plugin_theme_blocks' => [
        'content-share-bar' => ['after_post_content'],
        'before-footer-promo' => ['before_footer'],
        'instagram-feed' => ['after_hero'],
    ],
];
