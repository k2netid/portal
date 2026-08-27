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

        if (Form::query()->where('slug', 'contact')->exists()) {
            return;
        }

        $form = Form::query()->create([
            'name' => 'Contact',
            'slug' => 'contact',
            'description' => 'Public contact form for the site theme.',
            'success_message' => 'Thanks — we received your message.',
            'is_active' => true,
        ]);

        $fields = [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'sort_order' => 1, 'placeholder' => 'Your name'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'sort_order' => 2, 'placeholder' => 'name@site.com'],
            ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'sort_order' => 3, 'placeholder' => 'How can we help?'],
        ];

        foreach ($fields as $field) {
            FormField::query()->create([
                'form_id' => $form->id,
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'placeholder' => $field['placeholder'],
                'is_required' => true,
                'sort_order' => $field['sort_order'],
            ]);
        }
    }
}
