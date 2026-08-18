<?php

declare(strict_types=1);

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $extension_slug
 * @property string $action
 * @property string|null $version_before
 * @property string|null $version_after
 * @property string $status
 * @property string|null $error_message
 * @property string|null $performed_by
 * @property string $created_at
 */
class ExtensionLog extends Model
{
    protected $table = 'sys_extension_logs';

    public $timestamps = false;

    protected $fillable = [
        'extension_slug',
        'action',
        'version_before',
        'version_after',
        'status',
        'error_message',
        'performed_by',
    ];
}
