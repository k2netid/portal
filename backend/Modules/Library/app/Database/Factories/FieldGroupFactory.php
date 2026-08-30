<?php

declare(strict_types=1);

namespace Modules\Library\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Library\Models\FieldGroup;

/**
 * @extends Factory<FieldGroup>
 */
class FieldGroupFactory extends Factory
{
    protected $model = FieldGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
        ];
    }
}
