<?php

declare(strict_types=1);

namespace Modules\Core\System\Contracts;

interface LayoutRegistryInterface
{
    /**
     * Register menu locations for a module.
     *
     * @param  list<string>  $locations
     */
    public function registerMenuLocations(string $module, array $locations): void;

    /**
     * Get allowed menu locations for a module/theme.
     *
     * @return list<string>
     */
    public function getMenuLocations(string $module = 'publishing'): array;

    /**
     * Register widget locations for a module.
     *
     * @param  list<string>  $locations
     */
    public function registerWidgetLocations(string $module, array $locations): void;

    /**
     * Get allowed widget locations.
     *
     * @return list<string>
     */
    public function getWidgetLocations(string $module = 'publishing'): array;

    /**
     * Register widget types for a module.
     *
     * @param  array<string, string>  $types
     */
    public function registerWidgetTypes(string $module, array $types): void;

    /**
     * Get allowed widget types.
     *
     * @return array<string, string>
     */
    public function getWidgetTypes(string $module = 'publishing'): array;
}
