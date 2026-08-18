<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Modules\Core\System\Services\ModuleHealthProbe;

trait RespondsWithModuleHealth
{
    protected function moduleHealth(string $module, callable $checks): JsonResponse
    {
        $payload = app(ModuleHealthProbe::class)->run($module, $checks);
        $status = $payload['status'] === 'ok' ? 200 : 503;

        return response()->json([
            'success' => $payload['status'] === 'ok',
            'message' => $module.' module health',
            'data' => $payload,
        ], $status);
    }
}
