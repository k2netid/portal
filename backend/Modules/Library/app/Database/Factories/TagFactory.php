<?php

namespace Modules\Library\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Library\Models\Tag;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $nameRaw = fake()->unique()->words(1, true);
        $name = is_string($nameRaw) ? $nameRaw : implode(' ', $nameRaw);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => 'content',
            'author_id' => null,
        ];
    }
}
