<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Models\Setting;

class ConfigServerController extends BaseApiController
{
    /**
     * Resolve configuration for an external client.
     * This endpoint should be protected by the OAuth2 client credentials grant.
     */
    public function resolve(Request $request): JsonResponse
    {
        // For client credentials grant, we might not have a user,
        // but we have a valid access token for the machine/client.

        // Fetch all public settings
        $settings = Setting::where('is_public', true)->get();

        $config = [];
        foreach ($settings as $setting) {
            $config[$setting->group][$setting->key] = $this->castSettingValue($setting->value, $setting->type);
        }

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'data' => $config,
        ], 200);
    }

    /**
     * Webhook endpoint for clients to notify the server they want to sync configs,
     * or for the server to accept a ping.
     */
    public function sync(Request $request): JsonResponse
    {
        // Example implementation for a ping/sync acknowledgment
        return response()->json([
            'success' => true,
            'message' => 'Sync request acknowledged. Please call /resolve to fetch the latest configs.',
        ], 200);
    }

    protected function castSettingValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => is_numeric($value) ? (int) $value : 0,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => is_string($value) ? json_decode($value, true) : (is_array($value) ? $value : json_decode((string) json_encode($value), true)),
            default => $value,
        };
    }
}
