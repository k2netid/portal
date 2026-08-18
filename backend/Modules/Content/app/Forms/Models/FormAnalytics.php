<?php

namespace Modules\Content\Forms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $form_id
 * @property Carbon $date
 * @property int $views
 * @property int $starts
 * @property int $submissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Form $form
 */
class FormAnalytics extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'frm_form_analytics';

    protected $fillable = [
        'form_id',
        'date',
        'views',
        'starts',
        'submissions',
    ];

    protected $casts = [
        'date' => 'date',
        'views' => 'integer',
        'starts' => 'integer',
        'submissions' => 'integer',
    ];

    /**
     * @return BelongsTo<Form, $this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
