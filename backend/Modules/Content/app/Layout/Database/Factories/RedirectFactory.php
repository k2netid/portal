<?php

declare(strict_types=1);

namespace Modules\Content\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Layout\Models\Redirect;

/** @extends Factory<Redirect> */
class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'source_path' => '/'.fake()->unique()->slug(),
            'target_path' => '/'.fake()->slug(),
            'status_code' => fake()->randomElement([301, 302]),
            'module_scope' => 'publishing',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }

    public function permanent(): static
    {
        return $this->state(fn (array $attributes): array => ['status_code' => 301]);
    }

    public function temporary(): static
    {
        return $this->state(fn (array $attributes): array => ['status_code' => 302]);
    }
}
