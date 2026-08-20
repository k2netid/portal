<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Console;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Services\LicenseService;

class LicenseController extends BaseApiController
{
    public function __construct(
        private readonly LicenseService $licenseService
    ) {}

    /**
     * Get current license details, active tier, masked key, and feature matrix.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->licenseService->getLicenseStatus(),
        ]);
    }

    /**
     * Activate a license key via JA-CP or format verification.
     */
    public function activate(Request $request): JsonResponse
    {
        $request->validate([
            'license_key' => 'required|string|min:6|max:255',
        ]);

        $key = (string) $request->input('license_key');
        $result = $this->licenseService->activateLicense($key);

        $status = $result['success'] ? 200 : 422;

        return response()->json($result, $status);
    }

    /**
     * Sync license status and heartbeat with JA-CP.
     */
    public function refresh(): JsonResponse
    {
        $result = $this->licenseService->syncHeartbeat(true);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $this->licenseService->getLicenseStatus(),
        ]);
    }

    /**
     * Deactivate current license key and revert to Community edition.
     */
    public function deactivate(): JsonResponse
    {
        $result = $this->licenseService->deactivateLicense();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => $this->licenseService->getLicenseStatus(),
        ]);
    }
}
