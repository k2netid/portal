<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\User;
use Modules\Core\System\Services\ConsoleThemeService;

class ConsoleThemeController extends BaseApiController
{
    public function show(ConsoleThemeService $consoleTheme): JsonResponse
    {
        $user = request()->user();
        /** @var User|null $user */
        if (! $user) {
            return $this->unauthorized();
        }

        // Any authenticated console user may read appearance tokens (non-sensitive).

        return $this->success($consoleTheme->getPayload(), 'Console theme retrieved successfully');
    }

    /**
     * Read-only appearance tokens for Jejakawan (no operator session required).
     */
    public function showPublic(ConsoleThemeService $consoleTheme): JsonResponse
    {
        return $this->success(
            ['settings' => $consoleTheme->getResolvedSettings()],
            'Console theme retrieved successfully',
        );
    }
}
