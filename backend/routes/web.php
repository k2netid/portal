<?php

use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpaController::class, 'index']);

Route::any('/cdn-cgi/{path?}', [SpaController::class, 'cdnCgi'])->where('path', '.*');

$probePaths = [
    'admin',
    'admin/*',
    'dashboard',
    'dashboard/*',
    'panel',
    'panel/*',
    'wp-admin',
    'wp-admin/*',
    'wp-login.php',
    'phpmyadmin',
    'phpmyadmin/*',
    'pma',
    'cpanel',
    'administrator',
    'administrator/*',
    'manager',
    'manage',
    'system',
    'system/*',
];

Route::middleware('throttle:probe-paths')->any('/{path}', [SpaController::class, 'probe'])->whereIn('path', $probePaths);

Route::fallback([SpaController::class, 'fallback']);
