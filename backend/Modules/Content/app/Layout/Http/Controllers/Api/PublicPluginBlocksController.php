<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Content\Layout\Services\PluginThemeBlocksService;
use Modules\Core\System\Http\Controllers\BaseApiController;

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
