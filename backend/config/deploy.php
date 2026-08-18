<?php

declare(strict_types=1);

/**
 * Deploy plane for hosted Jejakawan (split ops vs tenant hosts).
 *
 * unified — single host (pilot); all routes active
 * ops     — control plane: platform admin, license, payment webhooks
 * tenant  — customer CMS, public site, member API
 */
return [
    'role' => env('APP_ROLE', 'unified'),
];
