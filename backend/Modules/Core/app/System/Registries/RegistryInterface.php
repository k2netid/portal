<?php

declare(strict_types=1);

namespace Modules\Core\System\Registries;

interface RegistryInterface
{
    /**
     * Register an item.
     */
    public function register(string $key, mixed $value): void;

    /**
     * Get an item by key.
     */
    public function get(string $key): mixed;

    /**
     * Get all registered items.
     *
     * @return array<string, mixed>
     */
    public function all(): array;
}
