<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;

/**
 * Optional Jejakawan bodies for Janari marketing pages (override theme defaults when published).
 */
class JanariMarketingPagesSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            return;
        }

        $pages = [
            'about' => [
                'title' => 'Tentang Jejakawan',
                'body' => '<p>Jejakawan membangun control plane untuk konten, tema Janari, intelligence, dan layanan komersial platform di satu hub.</p>',
            ],
            'solusi' => [
                'title' => 'Produk & solusi',
                'body' => '<p>Stack modul hub: Publishing, Layout, Forms, Intelligence, Platform, dan Jejakawan.</p>',
            ],
            'tim' => [
                'title' => 'Tim Jejakawan',
                'body' => '<p>Tim produk kecil yang mengirim engineering, konten, dan customer success.</p>',
            ],
        ];

        foreach ($pages as $slug => $data) {
            Content::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $data['title'],
                    'type' => 'page',
                    'status' => 'published',
                    'author_id' => $user->id,
                    'body' => $data['body'],
                ]
            );
        }

        $this->command->info('Janari marketing Jejakawan pages seeded (about, solusi, tim).');
    }
}
