<?php

namespace Modules\Content\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Content\Media\Models\File;

/**
 * @extends Factory<File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        $nameRaw = $this->faker->words(2, true);
        $name = is_string($nameRaw) ? $nameRaw : implode(' ', $nameRaw);
        $extension = $this->faker->fileExtension();
        $filename = Str::slug($name).'.'.$extension;

        return [
            'name' => $name,
            'file_name' => $filename,
            'mime_type' => $this->faker->mimeType(),
            'disk' => 'public',
            'path' => 'media/'.$filename,
            'size' => $this->faker->numberBetween(100, 1000000),
            'author_id' => null,
        ];
    }

    public function image(): self
    {
        return $this->state(fn (array $attributes): array => [
            'mime_type' => 'image/jpeg',
            'path' => 'media/'.Str::slug(is_string($attributes['name'] ?? null) ? $attributes['name'] : '').'.jpg',
        ]);
    }

    public function document(): self
    {
        return $this->state(fn (array $attributes): array => [
            'mime_type' => 'application/pdf',
            'path' => 'media/'.Str::slug(is_string($attributes['name'] ?? null) ? $attributes['name'] : '').'.pdf',
        ]);
    }
}
