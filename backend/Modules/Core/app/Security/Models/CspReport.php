<?php

declare(strict_types=1);

namespace Modules\Core\Security\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CspReport extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sec_csp_reports';

    protected $fillable = [
        'document_uri',
        'violated_directive',
        'blocked_uri',
        'source_file',
        'line_number',
        'user_agent',
        'ip_address',
        'raw_report',
        'status',
    ];

    protected $casts = [
        'raw_report' => 'array',
        'line_number' => 'integer',
    ];

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * @param  Builder<$this>  $query
     * @param  string  $directive
     * @return Builder<$this>
     */
    public function scopeByDirective($query, $directive)
    {
        return $query->where('violated_directive', $directive);
    }
}
