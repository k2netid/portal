<?php

declare(strict_types=1);

namespace Modules\Content\Library\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Content\Library\Models\Tag;

trait HasLibraryTags
{
    /**
     * @return MorphToMany<Tag, $this>
     */
    public function libraryTags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'lib_taggables');
    }

    /**
     * Attach tags to the model.
     *
     * @param  array<int|string>  $tags  Array of tag IDs or tag names
     */
    public function syncLibraryTags(array $tags): void
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            if (is_numeric($tag)) {
                $tagIds[] = (int) $tag;
            } elseif (is_string($tag) && trim($tag) !== '') {
                $tagModel = Tag::firstOrCreate([
                    'name' => trim($tag),
                ], [
                    'slug' => \Illuminate\Support\Str::slug(trim($tag)),
                ]);
                $tagIds[] = $tagModel->id;
            }
        }

        $this->libraryTags()->sync($tagIds);
    }
}
