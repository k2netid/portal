<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Layout\Models\Widget;

/**
 * @extends Factory<Widget>
 */
class WidgetFactory extends Factory
{
    protected $model = Widget::class;

    public function definition(): array
    {
        $nameRaw = fake()->words(2, true);
        $name = is_string($nameRaw) ? $nameRaw : implode(' ', $nameRaw);

        return [
            'name' => $name,
            'type' => 'text',
            'location' => 'sidebar',
            'content' => ['text' => fake()->paragraph()],
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
