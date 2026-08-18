<?php

namespace Modules\Content\Forms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Forms\Models\Form;
use Modules\Content\Forms\Models\FormSubmission;

/**
 * @extends Factory<FormSubmission>
 */
class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'user_id' => null,
            'data' => [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'message' => fake()->paragraph(),
            ],
            'status' => fake()->randomElement(['new', 'read', 'archived']),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    /**
     * Indicate that the submission is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'read',
        ]);
    }

    /**
     * Indicate that the submission is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'archived',
        ]);
    }
}
