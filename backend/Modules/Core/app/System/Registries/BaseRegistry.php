<?php

declare(strict_types=1);

namespace Modules\Core\System\Registries;

abstract class BaseRegistry implements RegistryInterface
{
    /** @var array<string, mixed> */
    protected array $items = [];

    public function register(string $key, mixed $value): void
    {
        $this->items[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }
}
