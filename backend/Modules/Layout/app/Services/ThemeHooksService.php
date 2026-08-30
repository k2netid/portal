<?php

namespace Modules\Layout\Services;

/**
 * Theme Hooks Service
 * Provides action and filter hooks for theme extensibility
 */
class ThemeHooksService
{
    /** @var array<string, array<int, array{callback: callable, priority: int}>> */
    protected array $actions = [];

    /** @var array<string, array<int, array{callback: callable, priority: int}>> */
    protected array $filters = [];

    /**
     * Register an action hook
     */
    public function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        if (! isset($this->actions[$hook])) {
            $this->actions[$hook] = [];
        }

        $this->actions[$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority
        usort($this->actions[$hook], fn (array $a, array $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Execute an action hook
     */
    public function doAction(string $hook, mixed ...$args): void
    {
        if (! isset($this->actions[$hook])) {
            return;
        }

        foreach ($this->actions[$hook] as $action) {
            call_user_func_array($action['callback'], $args);
        }
    }

    /**
     * Register a filter hook
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        if (! isset($this->filters[$hook])) {
            $this->filters[$hook] = [];
        }

        $this->filters[$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority
        usort($this->filters[$hook], fn (array $a, array $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Apply a filter hook
     *
     * @param  mixed  $value
     * @param  mixed  ...$args
     * @return mixed
     */
    public function applyFilter(string $hook, $value, ...$args)
    {
        if (! isset($this->filters[$hook])) {
            return $value;
        }

        foreach ($this->filters[$hook] as $filter) {
            $value = call_user_func_array($filter['callback'], array_merge([$value], $args));
        }

        return $value;
    }

    /**
     * Remove an action hook
     */
    public function removeAction(string $hook, callable $callback): void
    {
        if (! isset($this->actions[$hook])) {
            return;
        }

        $this->actions[$hook] = array_filter(
            $this->actions[$hook],
            fn (array $action) => $action['callback'] !== $callback
        );
    }

    /**
     * Remove a filter hook
     */
    public function removeFilter(string $hook, callable $callback): void
    {
        if (! isset($this->filters[$hook])) {
            return;
        }

        $this->filters[$hook] = array_filter(
            $this->filters[$hook],
            fn (array $filter) => $filter['callback'] !== $callback
        );
    }

    /**
     * Check if a hook has registered callbacks
     */
    public function hasAction(string $hook): bool
    {
        return isset($this->actions[$hook]) && (isset($this->actions[$hook]) && $this->actions[$hook] !== []);
    }

    /**
     * Check if a filter has registered callbacks
     */
    public function hasFilter(string $hook): bool
    {
        return isset($this->filters[$hook]) && (isset($this->filters[$hook]) && $this->filters[$hook] !== []);
    }

    /**
     * Get all registered hooks
     *
     * @return array{actions: array<int, string>, filters: array<int, string>}
     */
    public function getRegisteredHooks(): array
    {
        return [
            'actions' => array_keys($this->actions),
            'filters' => array_keys($this->filters),
        ];
    }
}
