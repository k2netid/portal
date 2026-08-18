<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

/**
 * @property string $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property string|null $description
 * @property bool $is_encrypted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
/**
 * @property string $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property string $group
 * @property string|null $description
 * @property bool $is_encrypted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class RedisSetting extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_redis_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get the value attribute with decryption if needed.
     */
    public function getValueAttribute(mixed $value): mixed
    {
        if ($this->is_encrypted && is_string($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Set the value attribute with encryption if needed.
     */
    public function setValueAttribute(mixed $value): void
    {
        if ($this->is_encrypted && is_string($value)) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['value'] = is_scalar($value) ? (string) $value : $value;
        }
    }

    /**
     * Get typed value based on type field.
     */
    public function getTypedValue(): mixed
    {
        $value = $this->value;

        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => is_numeric($value) ? (int) $value : 0,
            'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    /**
     * Get settings by group.
     *
     * @return Collection<string, mixed>
     */
    public static function getByGroup(string $group): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, self> $settings */
        $settings = static::where('group', $group)->get();

        return $settings->mapWithKeys(fn ($setting) => [(string) $setting->key => $setting->getTypedValue()]);
    }

    /**
     * Get single setting value.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        /** @var self|null $setting */
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->getTypedValue() : $default;
    }

    /**
     * Set setting value.
     */
    public static function setValue(string $key, mixed $value): ?self
    {
        /** @var self|null $setting */
        $setting = static::where('key', $key)->first();

        if ($setting) {
            $setting->value = is_scalar($value) ? (string) $value : (string) json_encode($value);
            $setting->save();
        }

        return $setting;
    }
}
