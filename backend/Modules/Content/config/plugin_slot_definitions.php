<?php

declare(strict_types=1);

/**
 * Admin + validation metadata for public theme plugin slots.
 * Keep in sync with frontend/src/engine/plugins/slot-manifest.json.
 */
return [
    'after_header' => [
        'label' => 'Below site header',
        'maxBlocks' => 5,
    ],
    'before_footer' => [
        'label' => 'Above site footer',
        'maxBlocks' => 5,
    ],
    'after_post_content' => [
        'label' => 'After article body',
        'maxBlocks' => 5,
    ],
];
