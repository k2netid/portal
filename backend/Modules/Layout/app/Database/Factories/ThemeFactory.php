<?php

namespace Modules\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Layout\Models\Theme;

/**
 * @extends Factory<Theme>
 */
class ThemeFactory extends Factory
{
    protected $model = Theme::class;

    public function definition(): array
    {
        $nameRaw = $this->faker->words(2, true);
        $name = is_string($nameRaw) ? $nameRaw : implode(' ', $nameRaw);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'type' => $this->faker->randomElement(['frontend', 'admin']),
            'path' => Str::slug($name),
            'version' => $this->faker->semver(),
            'description' => $this->faker->sentence(),
            'author' => $this->faker->name(),
            'author_url' => $this->faker->url(),
            'license' => 'MIT',
            'is_active' => false,
            'status' => 'active',
            'settings' => [],
            'dependencies' => [],
            'supports' => ['dark-mode', 'rtl'],
        ];
    }

    /**
     * Set theme as active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Set theme type
     */
    public function type(string $type): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type,
        ]);
    }
}
