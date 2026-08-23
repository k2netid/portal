<?php

declare(strict_types=1);

namespace Modules\Layout\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Layout\Services\PluginThemeBlocksValidator;
use Modules\Core\System\Http\Controllers\BaseApiController;

final class PluginThemeSlotsController extends BaseApiController
{
    public function index(PluginThemeBlocksValidator $validator): JsonResponse
    {
        $definitions = config('layout.plugin_slot_definitions', []);
        if (! is_array($definitions)) {
            $definitions = [];
        }

        $slots = [];
        foreach ($validator->allowedSlotIds() as $id) {
            $meta = is_array($definitions[$id] ?? null) ? $definitions[$id] : [];
            $slots[] = [
                'id' => $id,
                'label' => is_string($meta['label'] ?? null) ? $meta['label'] : $id,
                'maxBlocks' => is_numeric($meta['maxBlocks'] ?? null) ? (int) $meta['maxBlocks'] : 5,
            ];
        }

        $known = config('layout.plugin_theme_blocks', []);
        if (! is_array($known)) {
            $known = [];
        }

        return $this->success([
            'slots' => $slots,
            'known_plugins' => $known,
        ], 'Plugin theme slots retrieved');
    }
}
