<?php

declare(strict_types=1);

return [
    'control_plane_url' => env('JA_CP_URL', env('CONTROL_PLANE_URL', 'http://127.0.0.1:8080')),
    'api_key' => env('JA_CP_API_KEY', env('CONTROL_PLANE_API_KEY', '')),
    'timeout' => (int) env('JA_CP_TIMEOUT', 5),
    'webhook_secret' => env('JA_CP_WEBHOOK_SECRET', ''),
];
