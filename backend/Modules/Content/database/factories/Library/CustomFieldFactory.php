<?php

declare(strict_types=1);

namespace Modules\Content\Library\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Library\Models\CustomField;

/**
 * @extends Factory<CustomField>
 */
class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $key = fake()->unique()->slug(2);

        return [
            'name' => fake()->words(2, true),
            'key' => $key,
            'type' => 'text',
            'options' => null,
            'rules' => null,
            'default_value' => null,
            'placeholder' => null,
            'help_text' => null,
            'is_required' => false,
            'is_filterable' => false,
            'sort_order' => 0,
            'author_id' => null,
        ];
    }
}
