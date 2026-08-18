<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Publishing\Models\Content;
use Modules\Content\Publishing\Models\ContentRevision;
use Modules\Core\System\Models\User;

/**
 * @extends Factory<ContentRevision>
 */
class ContentRevisionFactory extends Factory
{
    protected $model = ContentRevision::class;

    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'author_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'body' => is_array($bodyRaw = $this->faker->paragraphs(3, true))
                ? implode("\n\n", $bodyRaw)
                : (string) $bodyRaw,
            'meta' => [
                'seo_title' => $this->faker->sentence(),
                'seo_description' => $this->faker->text(160),
                'revision_data' => [
                    'excerpt' => $this->faker->text(200),
                    'slug' => $this->faker->slug(),
                    'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
                ],
            ],
            'reason' => $this->faker->optional()->sentence(),
        ];
    }
}
