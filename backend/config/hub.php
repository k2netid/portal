<?php

declare(strict_types=1);

/**
 * Hub control plane — env bridge for `php artisan config:cache`.
 *
 * Laravel only reads env() from files under /config. Module configs
 * (`member`, `platform`, `publishing`) map these keys for runtime use.
 */
return [
    'member' => [
        'subscription_domain_header' => (string) env(
            'MEMBER_SUBSCRIPTION_DOMAIN_HEADER',
            (string) env('MEMBER_WORKSPACE_HEADER', 'X-Subscription-Domain'),
        ),
    ],

    'platform' => [
        'default_storage_limit_mb' => max(0, (int) env('PLATFORM_DEFAULT_STORAGE_LIMIT_MB', 10240)),
        'payment_webhook_skip_verify' => filter_var(env('PLATFORM_PAYMENT_WEBHOOK_SKIP_VERIFY', false), FILTER_VALIDATE_BOOL),
        'payment_internal_webhook_token' => (string) env('PLATFORM_PAYMENT_INTERNAL_WEBHOOK_TOKEN', ''),
        'payment_checkout_stub' => filter_var(env('PLATFORM_PAYMENT_CHECKOUT_STUB', false), FILTER_VALIDATE_BOOL),
        'midtrans_enabled' => filter_var(env('MIDTRANS_ENABLED', false), FILTER_VALIDATE_BOOL),
        'midtrans_server_key' => (string) env('MIDTRANS_SERVER_KEY', ''),
        'midtrans_is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOL),
        'xendit_enabled' => filter_var(env('XENDIT_ENABLED', false), FILTER_VALIDATE_BOOL),
        'xendit_callback_token' => (string) env('XENDIT_CALLBACK_TOKEN', ''),
        'xendit_secret_key' => (string) env('XENDIT_SECRET_KEY', ''),
    ],

    'publishing' => [
        'profile_public_theme_api' => filter_var(env('PROFILE_PUBLIC_THEME_API', false), FILTER_VALIDATE_BOOL),
        'public_active_theme_http_cache_max_age' => max(0, (int) env('PUBLISHING_PUBLIC_ACTIVE_THEME_HTTP_CACHE_MAX_AGE', 60)),
        'theme_views_relative_path' => env(
            'PUBLISHING_THEME_VIEWS_RELATIVE_PATH',
            '../frontend/src/modules/Content/Layout/views/themes',
        ),
    ],
];
