<?php

namespace Modules\Core\System\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Core\System\Traits\CoreLogsActivity;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $subject
 * @property string|null $body
 * @property string|null $text_body
 * @property array<string, mixed>|null $variables
 * @property string|null $category
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EmailTemplate extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'sys_email_templates';

    use CoreLogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'text_body',
        'variables',
        'category',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * @param  array<string, mixed>  $data
     * @return array{subject: string, body: string, text_body: string|null}
     */
    public function render(array $data = []): array
    {
        $subject = $this->replaceVariables((string) $this->subject, $data);
        $body = $this->replaceVariables((string) $this->body, $data);
        $textBody = $this->text_body ? $this->replaceVariables($this->text_body, $data) : null;

        return [
            'subject' => $subject,
            'body' => $body,
            'text_body' => $textBody,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function replaceVariables(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $template = str_replace('{{'.$key.'}}', (string) $value, $template);
                $template = str_replace('{{ $'.$key.' }}', (string) $value, $template);
            }
        }

        // Replace common variables
        $siteName = Setting::get('site_name', 'Jejakawan');
        $template = str_replace('{{ site_name }}', is_string($siteName) ? $siteName : 'Jejakawan', $template);
        $template = str_replace('{{ site_url }}', url('/'), $template);

        return str_replace('{{ current_year }}', date('Y'), $template);
    }

    public static function getBySlug(string $slug): ?self
    {
        /** @var self|null */
        return static::where('slug', $slug)->where('is_active', true)->first();
    }
}
