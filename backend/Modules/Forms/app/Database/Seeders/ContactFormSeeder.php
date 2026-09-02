<?php

declare(strict_types=1);

namespace Modules\Forms\Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormField;

class ContactFormSeeder
{
    public static function ensure(): void
    {
        if (! Schema::hasTable('frm_forms') || ! Schema::hasTable('frm_form_fields')) {
            return;
        }

        $form = Form::query()->where('slug', 'contact')->first();
        if ($form === null) {
            $form = Form::query()->create([
                'name' => 'Contact',
                'slug' => 'contact',
                'description' => 'Public contact form for the site theme.',
                'success_message' => 'Thanks — we received your message.',
                'is_active' => true,
            ]);

            foreach (self::defaultFields() as $field) {
                FormField::query()->create([
                    'form_id' => $form->id,
                    ...$field,
                    'is_required' => true,
                ]);
            }

            return;
        }

        self::ensurePhoneField($form);
    }

    /**
     * @return list<array{name: string, label: string, type: string, sort_order: int, placeholder: string}>
     */
    private static function defaultFields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'sort_order' => 1, 'placeholder' => 'Your name'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'sort_order' => 2, 'placeholder' => 'name@site.com'],
            ['name' => 'phone', 'label' => 'No. telp/WA', 'type' => 'text', 'sort_order' => 3, 'placeholder' => '08xxxxxxxxxx'],
            ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'sort_order' => 4, 'placeholder' => 'How can we help?'],
        ];
    }

    private static function ensurePhoneField(Form $form): void
    {
        if ($form->fields()->where('name', 'phone')->exists()) {
            return;
        }

        $message = $form->fields()->where('name', 'message')->first();
        $sort = $message !== null ? max(1, (int) $message->sort_order) : 3;
        if ($message !== null) {
            $message->update(['sort_order' => $sort + 1]);
        }

        FormField::query()->firstOrCreate(
            [
                'form_id' => $form->id,
                'name' => 'phone',
            ],
            [
                'label' => 'No. telp/WA',
                'type' => 'text',
                'placeholder' => '08xxxxxxxxxx',
                'is_required' => true,
                'sort_order' => $sort,
            ],
        );
    }
}
