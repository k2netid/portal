<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Layout\Models\Menu;
use Modules\Layout\Models\MenuItem;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        $titleRaw = fake()->words(2, true);
        $title = is_string($titleRaw) ? $titleRaw : implode(' ', $titleRaw);

        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'title' => $title,
            'url' => '/'.fake()->slug(),
            'type' => 'custom',
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
