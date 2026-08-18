<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Core\System\Database\Factories\LanguageFactory;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string $native_name
 * @property string|null $flag
 * @property bool $is_default
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool|null $has_ui_translations
 * @property int $translation_keys
 */
class Language extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_languages';

    /** @use HasFactory<LanguageFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): LanguageFactory
    {
        return LanguageFactory::new();
    }

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function getDefault(): ?self
    {
        /** @var self|null */
        return static::where('is_default', true)->where('is_active', true)->first();
    }

    /**
     * @return Collection<int, self>
     */
    public static function getActive(): Collection
    {
        return static::where('is_active', true)->orderBy('sort_order')->get();
    }
}
