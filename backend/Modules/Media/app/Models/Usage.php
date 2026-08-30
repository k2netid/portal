<?php

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Usage extends Model
{
    use HasUuids;

    protected $table = 'srv_media_usages';

    protected $fillable = [
        'file_id',
        'model_type',
        'model_id',
        'field_name',
    ];

    /**
     * @return BelongsTo<File, $this>
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Track a file usage.
     */
    public static function track(int|string $fileId, Model $model, string $fieldName): self
    {
        return self::updateOrCreate(
            [
                'model_type' => $model::class,
                'model_id' => $model->getKey(),
                'field_name' => $fieldName,
            ],
            [
                'file_id' => $fileId,
            ]
        );
    }

    /**
     * Untrack a file usage.
     */
    public static function untrack(int|string|null $fileId, Model $model, ?string $fieldName = null): void
    {
        $query = self::where('model_type', $model::class)
            ->where('model_id', $model->getKey());

        if ($fileId) {
            $query->where('file_id', $fileId);
        }

        if ($fieldName) {
            $query->where('field_name', $fieldName);
        }

        $query->delete();
    }
}
