<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Layout\Models\Menu;

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
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'location' => fake()->randomElement(['header', 'footer', 'sidebar']),
            'description' => fake()->sentence(),
            'module_scope' => 'publishing',
            'is_active' => true,
        ];
    }
}
