<?php

declare(strict_types=1);

namespace Modules\Layout\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Layout\Models\Redirect;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    protected $model = Redirect::class;

    public function definition(): array
    {
        return [
            'source_path' => '/'.fake()->slug(),
            'target_path' => '/'.fake()->slug(),
            'status_code' => 301,
            'module_scope' => 'publishing',
            'hits' => 0,
            'is_active' => true,
        ];
    }
}
