<?php

return [
    'challenge' => [
        'title' => 'Bot Shield Verification',
        'message' => 'Please wait while we verify that you are a human. This check helps protect our site from automated attacks.',
        'retry' => 'Try Again',
        'steps' => [
            'analyze' => 'Analyzing Connection',
            'solve' => 'Solving Challenge',
            'verify' => 'Verifying Response',
        ],
        'status' => [
            'initializing' => 'Initializing security check...',
            'analyzing' => 'Analyzing browser fingerprint...',
            'verifying' => 'Generating proof of work...',
            'finalizing' => 'Submitting verification...',
            'verified' => 'Verification successful! Redirecting...',
            'failed' => 'Verification failed. Please try again.',
        ],
    ],
];
