<?php

use App\Http\Controllers\Api\V1\InstallController;

Route::prefix('v1/install')->group(function () {
    Route::get('/status', [InstallController::class, 'getStatus']);
    Route::post('/', [InstallController::class, 'install']);
    Route::post('/setup-admin', [InstallController::class, 'postResetSetup']);
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes are encapsulated in their respective modules:
| - Modules/Core/routes/system_api.php
| - Modules/Core/routes/infra_api.php
| - Modules/Core/routes/security_api.php
|*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\System\Http\Controllers\Console\AuthController;

/*
| Legacy entrypoint: same JSON contract as GET /api/v1/user (canonical for SPA).
| Prefer /api/v1/user for new clients.
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $response = app(AuthController::class)->user($request);

    return $response->withHeaders([
        'Deprecation' => 'true',
        'Link' => '<'.url('/api/v1/user').'>; rel="successor-version"',
    ]);
});

// Fallback for users stranded from the previous maintenance strategy
Route::get('/maintenance', function () {
    if (app()->isDownForMaintenance()) {
        abort(503);
    }

    return redirect('/');
});
