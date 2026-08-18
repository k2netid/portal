<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

final class ModuleHealthProbe
{
    /**
     * @param  callable(): array<string, mixed>  $moduleChecks
     * @return array{module: string, status: string, checks: array<string, array{status: string, message?: string}>, checked_at: string}
     */
    public function run(string $module, callable $moduleChecks): array
    {
        $checks = [
            'database' => $this->database(),
        ];

        try {
            foreach ($moduleChecks() as $key => $result) {
                if (is_array($result)) {
                    /** @var array{status: string, message?: string} $res */
                    $res = $result;
                    $checks[$key] = $res;
                } else {
                    $checks[$key] = ['status' => 'ok'];
                }
            }
        } catch (Throwable $e) {
            $checks['module'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        $hasError = false;
        foreach ($checks as $check) {
            if (in_array($check['status'] ?? '', ['error'], true)) {
                $hasError = true;
                break;
            }
        }

        return [
            'module' => $module,
            'status' => $hasError ? 'degraded' : 'ok',
            'checks' => $checks,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /** @return array{status: string, message?: string} */
    private function database(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
