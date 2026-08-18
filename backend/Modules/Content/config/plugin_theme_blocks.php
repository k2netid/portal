<?php

declare(strict_types=1);

/**
 * Default public theme block slots for bundled plugins (slug => slot names).
 * DB plugin settings.theme_blocks overrides or extends this map.
 */
return [
    'content-share-bar' => ['after_post_content'],
    'before-footer-promo' => ['before_footer'],
];
