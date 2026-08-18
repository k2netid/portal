<?php

declare(strict_types=1);

namespace Modules\Core\System\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\System\Models\Language;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->languageCode(),
            'name' => fake()->word(),
            'native_name' => fake()->word(),
            'flag' => null,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
