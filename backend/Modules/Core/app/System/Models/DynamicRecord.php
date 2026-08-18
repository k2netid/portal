<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\Core\System\Models\DynamicRecord
 *
 * @property string $id
 * @property string $content_type_id
 * @property array<string, mixed>|null $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DynamicRecord extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_dynamic_records';

    protected $fillable = [
        'content_type_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the associated content type.
     *
     * @return BelongsTo<ContentType, $this>
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class, 'content_type_id');
    }
}
