<?php

declare(strict_types=1);

namespace Modules\Core\System\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Core\System\Http\Controllers\BaseApiController;
use Modules\Core\System\Services\SysMaintenanceService;

class MaintenanceApiController extends BaseApiController
{
    protected SysMaintenanceService $maintenanceService;

    public function __construct(SysMaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * POST /api/v1/manage/system/maintenance/clean-junk
     * Clean temporary ZIP uploads and stale extension caches.
     */
    public function cleanJunk(Request $request): JsonResponse
    {
        // Enforce super-admin checks for system-level actions
        if ($request->user() === null || ! $request->user()->hasRole('super')) {
            return $this->error('Akses Ditolak', 403);
        }

        $result = $this->maintenanceService->cleanJunk();

        return $this->success(
            $result,
            'Sistem berhasil dibersihkan dari file sampah temp.'
        );
    }

    /**
     * POST /api/v1/manage/system/maintenance/optimize-db
     * Optimize database table files and clean orphan dynamic records.
     */
    public function optimizeDatabase(Request $request): JsonResponse
    {
        if ($request->user() === null || ! $request->user()->hasRole('super')) {
            return $this->error('Akses Ditolak', 403);
        }

        $result = $this->maintenanceService->optimizeDatabase();

        return $this->success(
            $result,
            'Database dan indeks skema berhasil dioptimalisasi.'
        );
    }

    /**
     * POST /api/v1/manage/system/maintenance/boost
     * Recompile framework cached configuration files.
     */
    public function boost(Request $request): JsonResponse
    {
        if ($request->user() === null || ! $request->user()->hasRole('super')) {
            return $this->error('Akses Ditolak', 403);
        }

        $result = $this->maintenanceService->boostPerformance();

        return $this->success(
            $result,
            'Cache kompilasi router dan view berhasil dimuat.'
        );
    }

    /**
     * POST /api/v1/manage/system/maintenance/factory-reset
     * Securely reset the entire microkernel OS environment back to pristine factory defaults.
     */
    public function factoryReset(Request $request): JsonResponse
    {
        if ($request->user() === null || ! $request->user()->hasRole('super')) {
            return $this->error('Akses Ditolak', 403);
        }

        // Strict safety validation: Require active super-admin password check
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $password = $request->input('password');
        if (! is_string($password)) {
            return $this->error('Password harus berupa string.', 400);
        }

        // Match currently logged-in super admin password
        if (! Hash::check($password, $request->user()->password)) {
            return $this->error('Password konfirmasi tidak cocok. Otorisasi gagal.', 403);
        }

        $result = $this->maintenanceService->factoryReset();

        return $this->success(
            $result,
            'Sistem berhasil di-restore ke default pabrik.'
        );
    }

    private function validateSuperAdminPassword(Request $request): ?JsonResponse
    {
        if ($request->user() === null || ! $request->user()->hasRole('super')) {
            return $this->error('Akses Ditolak', 403);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validasi Gagal', 422, $validator->errors()->toArray());
        }

        $password = $request->input('password');
        if (! is_string($password)) {
            return $this->error('Password harus berupa string.', 400);
        }

        if (! Hash::check($password, $request->user()->password)) {
            return $this->error('Password konfirmasi tidak cocok. Otorisasi gagal.', 403);
        }

        return null;
    }

    public function factoryResetStep1(Request $request): JsonResponse
    {
        if ($errorResponse = $this->validateSuperAdminPassword($request)) {
            return $errorResponse;
        }

        $result = $this->maintenanceService->factoryResetStep1();

        return $this->success($result, 'Langkah 1 selesai: Sandbox dibersihkan.');
    }

    public function factoryResetStep2(Request $request): JsonResponse
    {
        if ($errorResponse = $this->validateSuperAdminPassword($request)) {
            return $errorResponse;
        }

        $result = $this->maintenanceService->factoryResetStep2();

        return $this->success($result, 'Langkah 2 selesai: Media & Log dihapus.');
    }

    public function factoryResetStep3(Request $request): JsonResponse
    {
        if ($errorResponse = $this->validateSuperAdminPassword($request)) {
            return $errorResponse;
        }

        $result = $this->maintenanceService->factoryResetStep3();

        return $this->success($result, 'Langkah 3 selesai: Database dikosongkan.');
    }

    public function checkWelcomePhase(Request $request): JsonResponse
    {
        if ($request->user() === null || ! $request->user()->hasRole('super')) {
            return response()->json(['active' => false]);
        }

        $flagPath = storage_path('app/.post_reset_welcome');

        return response()->json([
            'active' => File::exists($flagPath),
        ]);
    }

    public function seedFresh(Request $request): JsonResponse
    {
        if ($request->user() === null || ! $request->user()->hasRole('super')) {
            return $this->error('Akses Ditolak', 403);
        }

        set_time_limit(300);
        ini_set('max_execution_time', '300');
        Artisan::call('db:seed', ['--force' => true]);

        Cache::flush();

        $flagPath = storage_path('app/.post_reset_welcome');
        if (File::exists($flagPath)) {
            File::delete($flagPath);
        }

        return $this->success(null, 'Fresh seeding completed. Factory defaults applied.');
    }

    public function dismissWelcome(Request $request): JsonResponse
    {
        if ($request->user() === null || ! $request->user()->hasRole('super')) {
            return $this->error('Akses Ditolak', 403);
        }

        $flagPath = storage_path('app/.post_reset_welcome');
        if (File::exists($flagPath)) {
            File::delete($flagPath);
        }

        return $this->success(null, 'Welcome wizard dismissed.');
    }
}
