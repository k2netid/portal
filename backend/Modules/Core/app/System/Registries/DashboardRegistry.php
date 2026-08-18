<?php

declare(strict_types=1);

namespace Modules\Core\System\Registries;

class DashboardRegistry extends BaseRegistry
{
    /**
     * Register a dashboard widget.
     *
     * @param array{
     *   title: string,
     *   component: string,
     *   width: string,
     *   permissions?: list<string>,
     *   data_callback: callable|array{0: class-string, 1: string}|string
     * } $config
     */
    public function register(string $key, mixed $config): void
    {
        parent::register($key, $config);
    }
}
