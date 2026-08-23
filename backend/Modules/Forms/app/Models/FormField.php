<?php

namespace Modules\Forms\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Core\Security\Rules\SafeUrl;
use Modules\Forms\Database\Factories\FormFieldFactory;

/**
 * @property int $id
 * @property int $form_id
 * @property string $name
 * @property string $label
 * @property string $type
 * @property string|null $placeholder
 * @property string|null $help_text
 * @property array<string, mixed>|null $options
 * @property array<string, mixed>|null $validation_rules
 * @property bool $is_required
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Form $form
 */
class FormField extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /** @use HasFactory<FormFieldFactory> */

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): FormFieldFactory
    {
        return FormFieldFactory::new();
    }

    protected $table = 'frm_form_fields';

    protected $fillable = [
        'form_id',
        'name',
        'label',
        'type',
        'placeholder',
        'help_text',
        'options',
        'validation_rules',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * @return BelongsTo<Form, $this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * @return array<int, string>
     */
    public function getValidationRules(): array
    {
        $raw = is_array($this->validation_rules) ? $this->validation_rules : [];
        /** @var array<int, string> $rules */
        $rules = [];
        foreach ($raw as $item) {
            if (is_string($item)) {
                $rules[] = $item;
            }
        }

        if ($this->is_required) {
            $rules[] = 'required';
        }

        // Add type-specific rules
        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'url':
                $rules[] = SafeUrl::class;
                break;
            case 'number':
                $rules[] = 'numeric';
                break;
            case 'file':
                $rules[] = 'file';
                $rules[] = 'max:10240';
                break;
            case 'image':
                $rules[] = 'image';
                $rules[] = 'max:5120';
                break;
        }

        return $rules;
    }
}
