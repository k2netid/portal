<?php

namespace Modules\Core\System\Registries;

use Modules\Core\System\Contracts\LayoutRegistryInterface;

class LayoutRegistry implements LayoutRegistryInterface
{
    /** @var array<string, list<string>> */
    protected array $menuLocations = [];

    /** @var array<string, list<string>> */
    protected array $widgetLocations = [];

    /** @var array<string, array<string, string>> */
    protected array $widgetTypes = [];

    /**
     * @param  list<string>  $locations
     */
    public function registerMenuLocations(string $module, array $locations): void
    {
        $this->menuLocations[$module] = array_merge($this->menuLocations[$module] ?? [], $locations);
    }

    /**
     * @return list<string>
     */
    public function getMenuLocations(string $module = 'publishing'): array
    {
        return $this->menuLocations[$module] ?? [];
    }

    /**
     * @param  list<string>  $locations
     */
    public function registerWidgetLocations(string $module, array $locations): void
    {
        $this->widgetLocations[$module] = array_merge($this->widgetLocations[$module] ?? [], $locations);
    }

    /**
     * @return list<string>
     */
    public function getWidgetLocations(string $module = 'publishing'): array
    {
        return $this->widgetLocations[$module] ?? [];
    }

    /**
     * @param  array<string, string>  $types
     */
    public function registerWidgetTypes(string $module, array $types): void
    {
        $this->widgetTypes[$module] = array_merge($this->widgetTypes[$module] ?? [], $types);
    }

    /**
     * @return array<string, string>
     */
    public function getWidgetTypes(string $module = 'publishing'): array
    {
        return $this->widgetTypes[$module] ?? [
            'html' => 'Custom HTML',
            'content_list' => 'Content List',
            'menu' => 'Navigation Menu',
            'form' => 'Custom Form',
        ];
    }
}
