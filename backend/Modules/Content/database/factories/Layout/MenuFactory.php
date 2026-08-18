<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Layout\Models\Menu;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        $titleRaw = fake()->words(2, true);
        $title = is_string($titleRaw) ? $titleRaw : implode(' ', $titleRaw);

        return [
            'name' => $title,
            'location' => fake()->slug(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
