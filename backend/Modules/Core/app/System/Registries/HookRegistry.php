<?php

declare(strict_types=1);

namespace Modules\Core\System\Registries;

use Illuminate\Support\Facades\Log;

class HookRegistry extends BaseRegistry
{
    /**
     * Register a hook listener.
     *
     * @param  (callable(): mixed)|string  $callback
     */
    public function register(string $hookName, mixed $callback, int $priority = 10): void
    {
        if (! isset($this->items[$hookName]) || ! is_array($this->items[$hookName])) {
            $this->items[$hookName] = [];
        }

        /** @var array<int, array{callback: (callable(): mixed)|string, priority: int}> $hooks */
        $hooks = $this->items[$hookName];
        $hooks[] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        usort($hooks, fn (array $a, array $b): int => ((int) $a['priority']) <=> ((int) $b['priority']));

        $this->items[$hookName] = $hooks;
    }

    /**
     * Alias for register.
     *
     * @param  (callable(): mixed)|string  $callback
     */
    public function listen(string $hookName, mixed $callback, int $priority = 10): void
    {
        $this->register($hookName, $callback, $priority);
    }

    /**
     * Run all registered listeners for a specific action hook (fire-and-forget).
     *
     * @param  mixed  ...$params
     */
    public function action(string $actionName, ...$params): void
    {
        /** @var array<int, array{callback: (callable(): mixed)|string, priority: int}>|null $hooks */
        $hooks = $this->get($actionName);

        foreach ($hooks ?? [] as $hook) {
            $this->invoke($hook['callback'], array_values($params));
        }
    }

    /**
     * Run all registered listeners for a specific filter hook, passing the value sequentially.
     *
     * @param  mixed  ...$params
     */
    public function filter(string $filterName, mixed $value, ...$params): mixed
    {
        /** @var array<int, array{callback: (callable(): mixed)|string, priority: int}>|null $hooks */
        $hooks = $this->get($filterName);

        foreach ($hooks ?? [] as $hook) {
            $result = $this->invoke($hook['callback'], array_values(array_merge([$value], $params)));
            if (! ($result instanceof HookCrashToken)) {
                $value = $result;
            }
        }

        return $value;
    }

    /**
     * Run all registered listeners returning their output values.
     *
     * @param  mixed  ...$params
     * @return array<int, mixed>
     */
    public function run(string $hookName, ...$params): array
    {
        $results = [];
        /** @var array<int, array{callback: (callable(): mixed)|string, priority: int}>|null $hooks */
        $hooks = $this->get($hookName);

        foreach ($hooks ?? [] as $hook) {
            $result = $this->invoke($hook['callback'], array_values($params));
            if (! ($result instanceof HookCrashToken)) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Safely invoke hook callback, utilizing standard callable execution or Laravel container DI resolution.
     *
     * @param  (callable(): mixed)|string  $callback
     * @param  array<int, mixed>  $params
     */
    protected function invoke(mixed $callback, array $params): mixed
    {
        try {
            // Lazy Activation Engine: On-demand boot for plugin service providers before executing hooks
            if (is_string($callback)) {
                $callbackClean = ltrim($callback, '\\');
                if (str_starts_with($callbackClean, 'Extensions\\')) {
                    $parts = explode('\\', $callbackClean);
                    $studlyName = $parts[1] ?? null;
                    if ($studlyName) {
                        $providerClass = "Extensions\\{$studlyName}\\{$studlyName}ServiceProvider";
                        if (class_exists($providerClass)) {
                            app()->register($providerClass);
                        }
                    }
                }
            } elseif (is_array($callback) && isset($callback[0])) {
                $classOrObject = $callback[0];
                $class = is_object($classOrObject) ? get_class($classOrObject) : (is_string($classOrObject) ? $classOrObject : '');
                $classClean = ltrim($class, '\\');
                if (str_starts_with($classClean, 'Extensions\\')) {
                    $parts = explode('\\', $classClean);
                    $studlyName = $parts[1] ?? null;
                    if ($studlyName) {
                        $providerClass = "Extensions\\{$studlyName}\\{$studlyName}ServiceProvider";
                        if (class_exists($providerClass)) {
                            app()->register($providerClass);
                        }
                    }
                }
            }

            if (is_callable($callback)) {
                return call_user_func_array($callback, $params);
            }

            if (is_string($callback)) {
                // Laravel's Container::call resolves parameter names dynamically,
                // but PHPStan wants array<string, mixed> if non-indexed.
                // We cast to keep PHPStan level 9 satisfied.
                /** @var array<string, mixed> $assocParams */
                $assocParams = $params;

                return app()->call($callback, $assocParams);
            }

            return null;
        } catch (\Throwable $e) {
            // IPC Error Isolation: Log the crash and isolate it to prevent system-wide segmentation fault
            Log::error(sprintf(
                'Kernel IPC Error [Segmentation Fault]: Hook callback crashed. Message: %s. File: %s:%d. Context: %s',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                is_string($callback) ? $callback : (is_array($callback) ? (string) json_encode($callback) : 'Closure')
            ));

            return new HookCrashToken($e);
        }
    }
}
