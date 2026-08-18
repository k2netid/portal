<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'scim' => [
        'token' => env('SCIM_TOKEN'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sme' => [
        'response_signing_secret' => env('SME_RESPONSE_SIGNING_SECRET', ''),
        'offline_license_hmac_secret' => env('SME_OFFLINE_LICENSE_HMAC_SECRET', ''),
    ],

    'aksara' => [
        'api_url' => rtrim((string) env('AKSARA_API_URL', 'https://api-aksara.jejakawan.com'), '/'),
        'app_url' => rtrim((string) env('AKSARA_APP_URL', 'https://aksarakita.jejakawan.com'), '/'),
        'entitlement_secret' => env('AKSARA_ENTITLEMENT_SECRET', ''),
    ],

    'exambro' => [
        'download_url' => env('EXAMBRO_DOWNLOAD_URL', 'https://github.com/jejak-awan/ja-exambro/releases'),
        'docs_url' => env('EXAMBRO_DOCS_URL', 'https://github.com/jejak-awan/ja-exambro/blob/android/README.md'),
    ],

];
