<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Extension;
use Throwable;

/**
 * Runs manifest lifecycle seeders on activate/deactivate (idempotent).
 */
class ExtensionLifecycleOrchestrator
{
    /**
     * @return list<string> seeder classes that ran successfully
     */
    public function runActivateSeeders(Extension $extension): array
    {
        return $this->runSeeders($extension, 'seeders_on_activate');
    }

    /**
     * @return list<string> seeder classes that ran successfully
     */
    public function runDeactivateSeeders(Extension $extension): array
    {
        return $this->runSeeders($extension, 'seeders_on_deactivate');
    }

    /**
     * @return list<string>
     */
    private function runSeeders(Extension $extension, string $key): array
    {
        $ran = [];
        foreach ($this->seedersFor($extension, $key) as $class) {
            try {
                $this->invokeSeeder($class);
                $ran[] = $class;
            } catch (Throwable $e) {
                Log::warning('Extension lifecycle seeder failed', [
                    'extension' => $extension->slug,
                    'seeder' => $class,
                    'phase' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $ran;
    }

    /**
     * @return list<string>
     */
    private function seedersFor(Extension $extension, string $key): array
    {
        $manifest = $this->manifest($extension);
        $lifecycle = $manifest['lifecycle'] ?? null;
        if (! is_array($lifecycle)) {
            return [];
        }

        $raw = $lifecycle[$key] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $classes = [];
        foreach ($raw as $class) {
            if (! is_string($class) || trim($class) === '') {
                continue;
            }
            if (! $this->isAllowedSeederClass($class)) {
                Log::warning('Extension lifecycle seeder skipped (not allowlisted)', [
                    'extension' => $extension->slug,
                    'seeder' => $class,
                ]);

                continue;
            }
            $classes[] = $class;
        }

        return array_values(array_unique($classes));
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(Extension $extension): array
    {
        if (is_array($extension->manifest) && $extension->manifest !== []) {
            return $extension->manifest;
        }

        $slug = (string) $extension->slug;
        $path = base_path('Modules/'.str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))).'/manifest.json');
        if (! is_file($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function isAllowedSeederClass(string $class): bool
    {
        if (! str_starts_with($class, 'Modules\\')) {
            return false;
        }

        return class_exists($class);
    }

    private function invokeSeeder(string $class): void
    {
        if (method_exists($class, 'ensure')) {
            $class::ensure();

            return;
        }

        $instance = app($class);
        if (method_exists($instance, 'run')) {
            $instance->run();
        }
    }
}
