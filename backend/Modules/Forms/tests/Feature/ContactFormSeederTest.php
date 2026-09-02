<?php

declare(strict_types=1);

namespace Modules\Forms\Tests\Feature;

use Modules\Core\System\Models\Extension;
use Modules\Forms\Database\Seeders\ContactFormSeeder;
use Modules\Forms\Models\Form;
use Tests\TestCase;

class ContactFormSeederTest extends TestCase
{
    public function test_ensure_creates_contact_form_once(): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => 'forms'],
            [
                'type' => 'module',
                'name' => 'Forms',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_core' => false,
            ]
        );

        ContactFormSeeder::ensure();
        ContactFormSeeder::ensure();

        $this->assertSame(1, Form::query()->where('slug', 'contact')->count());
        $form = Form::query()->where('slug', 'contact')->first();
        $this->assertNotNull($form);
        $this->assertSame(4, $form->fields()->count());
        $this->assertTrue($form->fields()->where('name', 'phone')->where('is_required', true)->exists());
    }

    public function test_ensure_adds_required_phone_to_existing_contact_form(): void
    {
        Extension::query()->updateOrCreate(
            ['slug' => 'forms'],
            [
                'type' => 'module',
                'name' => 'Forms',
                'version' => '1.0.0',
                'database_version' => '1.0.0',
                'status' => 'active',
                'is_core' => false,
            ]
        );

        ContactFormSeeder::ensure();
        $form = Form::query()->where('slug', 'contact')->first();
        $this->assertNotNull($form);
        $form->fields()->where('name', 'phone')->delete();
        $this->assertSame(3, $form->fields()->count());

        ContactFormSeeder::ensure();

        $this->assertTrue($form->fields()->where('name', 'phone')->where('is_required', true)->exists());
        $this->assertSame(4, $form->fields()->count());
    }
}
