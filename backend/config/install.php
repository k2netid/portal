<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Install profile
    |--------------------------------------------------------------------------
    |
    | Applied after migrate --seed / ja:install / InstallController.
    | Operators without code access should never need tinker to get a sane boot.
    |
    | core      — kernel only; apex `/` = kernel landing (console login at /auth/console-*)
    | cms       — CMS editorial packs active; apex `/` still landing (Site off)
    | cms_site  — CMS + Site; apex `/` = public theme overrides landing; console at /auth/console-* + /dash
    |
    | Default: cms_site when Modules/Site exists (this product ships CMS packs),
    | otherwise core. Override with INSTALL_PROFILE=.
    |
    */
    'profile' => env(
        'INSTALL_PROFILE',
        is_file(dirname(__DIR__).'/Modules/Site/manifest.json') ? 'cms_site' : 'core'
    ),

    /*
    | Local / testing installs skip pack license blockers so Site (pro tier)
    | can activate without a JA-CP key. Production always enforces license.
    */
    'skip_license_checks' => env(
        'INSTALL_SKIP_LICENSE_CHECKS',
        in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)
    ),

    /*
    | Opt-in demo domain rows (e.g. Publishing welcome post). Never forced on production cms_site.
    */
    'seed_demo' => filter_var(env('INSTALL_SEED_DEMO', false), FILTER_VALIDATE_BOOL),
];
