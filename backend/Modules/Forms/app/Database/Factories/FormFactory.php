<?php

namespace Modules\Forms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Forms\Models\Form;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameRaw = fake()->words(3, true);
        $name = is_string($nameRaw) ? $nameRaw : implode(' ', $nameRaw);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.uniqid(),
            'description' => fake()->paragraph(),
            'success_message' => 'Thank you for your submission!',
            'redirect_url' => null,
            'settings' => [],
            'is_active' => true,
            'submission_count' => 0,
        ];
    }

    /**
     * Indicate that the form is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
