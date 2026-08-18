<?php

namespace Modules\Content\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Forms\Models\Form;
use Modules\Content\Forms\Models\FormSubmission;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Publishing\Contracts\NewsletterSampleDataPortInterface;
use Modules\Content\Publishing\Models\Comment;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;

class PublishingSampleDataSeeder extends Seeder
{
    /**
     * Run the sample data seeds for development.
     */
    public function run(): void
    {
        $emailRaw = config('app.super_admin_email', 'super@jejakawan.com');
        $email = is_scalar($emailRaw) ? (string) $emailRaw : 'super@jejakawan.com';
        $admin = User::where('email', $email)->first();
        if (! $admin) {
            return;
        }

        $categories = Category::all();
        $tags = Tag::all();

        // 1. Sample Blog Posts
        $posts = [
            [
                'title' => 'The Future of AI in Content Management',
                'slug' => 'future-of-ai-Jejakawan',
                'excerpt' => 'How machine learning is revolutionizing the way we create and manage web content.',
                'body' => '<p>Artificial Intelligence is no longer just a buzzword. In the realm of Content Management Systems (Jejakawan), AI is driving significant efficiencies...</p>',
                'is_featured' => true,
            ],
            [
                'title' => 'Mastering Modern Jejakawan Aesthetics',
                'slug' => 'mastering-Jejakawan-aesthetics',
                'excerpt' => 'A comprehensive guide to building beautiful layouts with clean, semantic HTML and CSS.',
                'body' => '<p>Good design is not just about how it looks, but how it works. By using clean structure...</p>',
                'is_featured' => false,
            ],
            [
                'title' => 'Top 10 Design Trends for 2026',
                'slug' => 'design-trends-2026',
                'excerpt' => 'Stay ahead of the curve with these emerging web design patterns and aesthetics.',
                'body' => '<p>From glassmorphism to advanced micro-interactions, 2026 is shaping up to be a year of bold experiments in visual storytelling...</p>',
                'is_featured' => true,
            ],
        ];

        foreach ($posts as $postData) {
            if ($categories->isNotEmpty()) {
                $postData['category_id'] = $categories->random()->id;
            }

            $content = Content::updateOrCreate(
                ['slug' => $postData['slug']],
                array_merge($postData, [
                    'author_id' => $admin->id,
                    'status' => 'published',
                    'published_at' => now()->subDays(random_int(1, 30)),
                ])
            );

            // Attach random tags
            if ($tags->isNotEmpty()) {
                $content->tags()->sync($tags->random(min($tags->count(), random_int(2, 4)))->pluck('id'));
            }

            // Add sample comments
            for ($i = 0; $i < random_int(1, 4); $i++) {
                Comment::create([
                    'content_id' => $content->id,
                    'name' => 'Sample Commenter '.($i + 1),
                    'email' => 'commenter'.($i + 1).'@example.com',
                    'body' => 'This is a great article on '.$postData['title'].'! Very helpful.',
                    'status' => 'approved',
                ]);
            }
        }

        // 2. Sample Newsletter Subscribers (via port — no direct Intelligence model import)
        $newsletterPort = app(NewsletterSampleDataPortInterface::class);
        for ($i = 0; $i < 10; $i++) {
            $newsletterPort->upsertSubscriberByEmail("user{$i}@example.com", [
                'name' => "Sample User {$i}",
                'status' => 'subscribed',
                'subscribed_at' => now()->subDays(random_int(1, 100)),
            ]);
        }

        // 3. Sample Form Submissions
        $contactForm = Form::where('slug', 'contact-form')->first();
        if ($contactForm) {
            $submissions = [
                [
                    'data' => ['name' => 'Alice Johnson', 'email' => 'alice@example.com', 'message' => 'I would like to inquire about your enterprise pricing.'],
                    'ip_address' => '192.168.1.10',
                    'created_at' => now()->subHours(2),
                ],
                [
                    'data' => ['name' => 'Bob Smith', 'email' => 'bob@example.com', 'message' => 'Great Jejakawan! How do I build a custom theme?'],
                    'ip_address' => '192.168.1.11',
                    'created_at' => now()->subDays(1),
                ],
                [
                    'data' => ['name' => 'Charlie Day', 'email' => 'charlie@example.com', 'message' => 'Found a bug in the mobile menu.'],
                    'ip_address' => '192.168.1.12',
                    'created_at' => now()->subDays(3),
                ],
            ];

            foreach ($submissions as $sub) {
                FormSubmission::create([
                    'form_id' => $contactForm->id,
                    'data' => $sub['data'],
                    'ip_address' => $sub['ip_address'],
                    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                    'status' => 'new',
                    'created_at' => $sub['created_at'],
                    'updated_at' => $sub['created_at'],
                ]);
            }
        }

        $this->command->info('Sample data seeded successfully!');
    }
}
