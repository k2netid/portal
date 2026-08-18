<?php

namespace Modules\Content\Library\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Content\Library\Models\Category;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameRaw = fake()->words(2, true);
        $name = is_string($nameRaw) ? $nameRaw : implode(' ', $nameRaw);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_active' => true,
            'parent_id' => null,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the category has a parent.
     */
    public function withParent(?Category $parent = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent !== null ? $parent->id : Category::factory()->create()->id,
        ]);
    }
}
