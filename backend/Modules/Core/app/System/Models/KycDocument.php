<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $submission_id
 * @property string $user_id
 * @property string $type
 * @property string $file_path
 * @property string $mime_type
 * @property int $size_bytes
 * @property string $original_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read KycSubmission $submission
 * @property-read User $user
 */
class KycDocument extends Model
{
    use HasUuids;

    public const TYPE_ID_CARD = 'id_card';

    public const TYPE_PASSPORT = 'passport';

    public const TYPE_SELFIE = 'selfie';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'kyc_documents';

    protected $fillable = [
        'submission_id',
        'user_id',
        'type',
        'file_path',
        'mime_type',
        'size_bytes',
        'original_name',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<KycSubmission, $this>
     */
    /**
     * @return BelongsTo<KycSubmission, $this>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(KycSubmission::class, 'submission_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
