<?php

namespace Modules\Content\Forms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Forms\Models\Form;
use Modules\Content\Forms\Models\FormField;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['text', 'email', 'textarea', 'select', 'checkbox', 'radio', 'number', 'date', 'file'];
        $type = fake()->randomElement($types);
        $labelRaw = fake()->words(2, true);
        $label = is_string($labelRaw) ? $labelRaw : implode(' ', $labelRaw);

        return [
            'form_id' => Form::factory(),
            'name' => fake()->unique()->slug(2),
            'label' => $label,
            'type' => $type,
            'placeholder' => fake()->optional()->sentence(3),
            'help_text' => fake()->optional()->sentence(),
            'options' => in_array($type, ['select', 'radio', 'checkbox']) ? [
                ['label' => 'Option 1', 'value' => 'option_1'],
                ['label' => 'Option 2', 'value' => 'option_2'],
                ['label' => 'Option 3', 'value' => 'option_3'],
            ] : null,
            'validation_rules' => [],
            'is_required' => fake()->boolean(70),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the field is required.
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => true,
        ]);
    }

    /**
     * Indicate that the field is optional.
     */
    public function optional(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => false,
        ]);
    }

    /**
     * Set field type to text.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'text',
            'options' => null,
        ]);
    }

    /**
     * Set field type to email.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'email',
            'options' => null,
        ]);
    }

    /**
     * Set field type to select with options.
     *
     * @param  list<array{label: string, value: string}>  $options
     */
    public function select(array $options = []): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'select',
            'options' => $options ?: [
                ['label' => 'Option A', 'value' => 'a'],
                ['label' => 'Option B', 'value' => 'b'],
            ],
        ]);
    }
}
