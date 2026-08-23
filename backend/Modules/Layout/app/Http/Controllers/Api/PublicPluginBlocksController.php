<?php

declare(strict_types=1);

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Layout\Services\PluginThemeBlocksService;

class PublicPluginBlocksController extends BaseApiController
{
    public function index(PluginThemeBlocksService $blocks): JsonResponse
    {
        /** @var array<string, mixed> $slots */
        $slots = config('layout.plugin_theme_blocks', []);
        if (! is_array($slots)) {
            $slots = [];
        }

        return $this->success([
            'plugins' => $blocks->getPublicManifest(),
            'slots' => array_keys($slots),
        ], 'Plugin theme blocks manifest retrieved');
    }
}
