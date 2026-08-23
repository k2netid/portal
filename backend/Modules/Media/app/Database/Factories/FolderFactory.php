<?php

namespace Modules\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Media\Models\Folder;

/**
 * @extends Factory<Folder>
 */
class FolderFactory extends Factory
{
    protected $model = Folder::class;

    public function definition(): array
    {
        $nameRaw = $this->faker->words(2, true);
        $name = is_string($nameRaw) ? $nameRaw : implode(' ', $nameRaw);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'author_id' => null,
            'module' => 'publishing',
        ];
    }
}
