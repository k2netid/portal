<?php

use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpaController::class, 'index']);

Route::get('/site/{any?}', [SpaController::class, 'publicSite'])->where('any', '.*');

Route::any('/cdn-cgi/{path?}', [SpaController::class, 'cdnCgi'])->where('path', '.*');

$probePaths = [
    'admin',
    'dashboard',
    'panel',
    'wp-admin',
    'wp-login.php',
    'phpmyadmin',
    'pma',
    'cpanel',
    'administrator',
    'manager',
    'manage',
    'system',
];

// Case-insensitive exact first-segment probes (AdMiN, DaShBoArD, …).
$probePattern = '(?i)('.implode('|', array_map(
    static fn (string $p): string => preg_quote($p, '/'),
    $probePaths,
)).')';

Route::middleware('throttle:probe-paths')->any('/{path}', [SpaController::class, 'probe'])->where('path', $probePattern);

Route::fallback([SpaController::class, 'fallback']);
