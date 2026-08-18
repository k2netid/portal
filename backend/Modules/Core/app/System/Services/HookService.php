<?php

namespace Modules\Core\System\Services;

class HookService
{
    /** @var array<string, array<int, array{callback: callable, priority: int}>> */
    protected static $hooks = [];

    /**
     * Register a hook
     */
    public static function addHook(string $hookName, callable $callback, int $priority = 10): void
    {
        if (! isset(self::$hooks[$hookName])) {
            self::$hooks[$hookName] = [];
        }

        self::$hooks[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority
        usort(self::$hooks[$hookName], fn (array $a, array $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Execute a hook
     */
    public static function doHook(string $hookName, mixed ...$args): mixed
    {
        if (! isset(self::$hooks[$hookName])) {
            return $args[0] ?? null;
        }

        $result = $args[0] ?? null;

        foreach (self::$hooks[$hookName] as $hook) {
            $result = call_user_func($hook['callback'], $result, ...$args);
        }

        return $result;
    }

    /**
     * Apply a filter
     */
    public static function applyFilter(string $filterName, mixed $value, mixed ...$args): mixed
    {
        return self::doHook($filterName, $value, ...$args);
    }

    /**
     * Get all registered hooks
     *
     * @return array<string, array<int, array{callback: callable, priority: int}>>
     */
    public static function getHooks(): array
    {
        return self::$hooks;
    }

    /**
     * Remove a hook
     */
    public static function removeHook(string $hookName, ?callable $callback = null): void
    {
        if (! isset(self::$hooks[$hookName])) {
            return;
        }

        if ($callback === null) {
            unset(self::$hooks[$hookName]);

            return;
        }

        self::$hooks[$hookName] = array_filter(
            self::$hooks[$hookName],
            fn (array $hook) => $hook['callback'] !== $callback
        );
    }
}
