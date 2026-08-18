<?php

namespace Modules\Content\Publishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Content\Layout\Database\Seeders\JanariHubMenuSeeder;
use Modules\Content\Library\Models\Category;
use Modules\Content\Library\Models\Tag;
use Modules\Content\Media\Models\Folder;
use Modules\Content\Publishing\Models\Comment;
use Modules\Content\Publishing\Models\Content;
use Modules\Core\System\Models\User;

class SampleContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample content...');

        $user = User::first();
        if (! $user instanceof User) {
            $this->command->warn('No user found; skipping sample content.');

            return;
        }

        // Create categories
        $categories = $this->createCategories();

        // Create tags
        $tags = $this->createTags();

        // Create menus
        $this->createMenus();

        // Create pages
        $this->createLandingPage($user);
        $this->createAboutPage($user);
        $this->createContactPage($user);

        // Create blog posts
        $this->createBlogPosts($user, $categories, $tags);

        // Create media folders
        $this->createMediaFolders($user);

        // Create comments
        $this->createComments($user);

        $this->command->info('Sample content created successfully!');
    }

    /**
     * @return array<string, Category>
     */
    private function createCategories(): array
    {
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Latest tech news and tutorials'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'UI/UX and design inspiration'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business insights and strategies'],
            ['name' => 'Tutorial', 'slug' => 'tutorial', 'description' => 'Step-by-step guides'],
        ];

        $result = [];
        foreach ($categories as $cat) {
            $result[$cat['slug']] = Category::firstOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        return $result;
    }

    /**
     * @return list<Tag>
     */
    private function createTags(): array
    {
        $tagNames = ['Laravel', 'Vue.js', 'Jejakawan', 'Web Development', 'Design', 'Tutorial', 'Guide', 'Tips'];
        $result = [];

        foreach ($tagNames as $name) {
            $result[] = Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'slug' => Str::slug($name)]
            );
        }

        return $result;
    }

    private function createMenus(): void
    {
        $this->call(JanariHubMenuSeeder::class);

    }

    private function createLandingPage(User $user): void
    {
        [
            // Hero Section
            [
                'id' => Str::uuid()->toString(),
                'type' => 'hero',
                'settings' => [
                    'title' => 'Build Amazing Websites with Jejakawan',
                    'subtitle' => 'The modern content management system that empowers you to create stunning, fast, and SEO-friendly websites without writing code.',
                    'bgImage' => '',
                    'bgColor' => '#4f46e5',
                    'padding' => 'py-32',
                    'radius' => 'rounded-none',
                    'animation' => 'animate-in fade-in duration-700',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
            // Features Section
            [
                'id' => Str::uuid()->toString(),
                'type' => 'features',
                'settings' => [
                    'title' => 'Why Choose Jejakawan?',
                    'items' => [
                        ['title' => 'Menu Builder', 'description' => 'Create complex navigation structures with ease.'],
                        ['title' => 'SEO Optimized', 'description' => 'Built-in SEO tools to help your content rank higher.'],
                        ['title' => 'Multi-language', 'description' => 'Full i18n support for global audiences.'],
                        ['title' => 'Dark Mode', 'description' => 'Beautiful dark mode support out of the box.'],
                        ['title' => 'Highly Customizable', 'description' => 'Easily customize every aspect of your site.'],
                        ['title' => 'Performant', 'description' => 'Fast page load speeds and optimized assets.'],
                    ],
                    'padding' => 'py-20',
                    'bgColor' => 'transparent',
                    'radius' => 'rounded-none',
                    'animation' => 'animate-in fade-in duration-700',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
            // Testimonial Section
            [
                'id' => Str::uuid()->toString(),
                'type' => 'testimonial',
                'settings' => [
                    'quote' => 'Jejakawan transformed how we manage our content. The system is incredibly intuitive and our team loves it!',
                    'author' => 'Sarah Johnson',
                    'role' => 'Marketing Director at TechCorp',
                    'avatar' => '',
                    'bgColor' => '#f8fafc',
                    'padding' => 'py-24',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
            // Blog Grid Section
            [
                'id' => Str::uuid()->toString(),
                'type' => 'blog-grid',
                'settings' => [
                    'title' => 'Latest from Our Blog',
                    'columns' => 3,
                    'limit' => 3,
                    'showExcerpt' => true,
                    'showDate' => true,
                    'showCategory' => true,
                    'padding' => 'py-20',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
            // CTA Section
            [
                'id' => Str::uuid()->toString(),
                'type' => 'cta',
                'settings' => [
                    'title' => 'Ready to Get Started?',
                    'subtitle' => 'Join thousands of creators and businesses using Jejakawan to build their online presence.',
                    'buttonText' => 'Start Free Trial',
                    'buttonUrl' => '/register',
                    'bgColor' => '#4f46e5',
                    'padding' => 'py-32',
                    'radius' => 'rounded-2xl',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
        ];

        Content::updateOrCreate(
            ['slug' => 'home', 'type' => 'page'],
            [
                'title' => 'Welcome to Jejakawan',
                'slug' => 'home',
                'type' => 'page',
                'status' => 'published',
                'body' => '<h1>Build Amazing Websites with Jejakawan</h1><p>The modern content management system that empowers you to create stunning, fast, and SEO-friendly websites.</p>',
                'author_id' => $user->id,
                'published_at' => now(),
                'meta_title' => 'Jejakawan - Modern Content Management System',
                'meta_description' => 'Build amazing websites with Jejakawan, the modern content management system.',
            ]
        );

        $this->command->info('Landing page created');
    }

    private function createAboutPage(User $user): void
    {
        [
            // Hero
            [
                'id' => Str::uuid()->toString(),
                'type' => 'hero',
                'settings' => [
                    'title' => 'About Jejakawan',
                    'subtitle' => 'We are building the future of content management - simple, powerful, and beautiful.',
                    'bgColor' => '#1e293b',
                    'padding' => 'py-24',
                    'animation' => 'animate-in fade-in duration-700',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
            // Text Block - Our Story
            [
                'id' => Str::uuid()->toString(),
                'type' => 'text',
                'settings' => [
                    'content' => '<h2>Our Story</h2><p>Jejakawan was born from a simple idea: content management should be easy, fast, and beautiful. We believe that everyone deserves powerful tools to share their ideas with the world.</p><p>Our team of passionate developers and designers work tirelessly to create the best possible experience for our users. We combine modern technology with intuitive design to deliver a Jejakawan that just works.</p>',
                    'padding' => 'py-16',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
            // Team Section
            [
                'id' => Str::uuid()->toString(),
                'type' => 'person',
                'settings' => [
                    'name' => 'The Jejakawan Team',
                    'role' => 'Building the Future',
                    'bio' => 'A dedicated team of developers, designers, and content strategists committed to making content management accessible to everyone.',
                    'avatar' => '',
                    'padding' => 'py-16',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
            // Features
            [
                'id' => Str::uuid()->toString(),
                'type' => 'features',
                'settings' => [
                    'title' => 'Our Values',
                    'items' => [
                        ['title' => 'Simplicity', 'description' => 'We believe powerful tools can also be easy to use.'],
                        ['title' => 'Performance', 'description' => 'Speed is not an afterthought - it is built into everything we do.'],
                        ['title' => 'Open Source', 'description' => 'We embrace transparency and community collaboration.'],
                    ],
                    'padding' => 'py-20',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
        ];

        Content::updateOrCreate(
            ['slug' => 'about', 'type' => 'page'],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'type' => 'page',
                'status' => 'published',
                'body' => null, // Let theme default handle this for better aesthetics
                'author_id' => $user->id,
                'published_at' => now(),
                'meta_title' => 'About Jejakawan - Our Story',
                'meta_description' => 'Learn about Jejakawan, our mission, and the team behind the modern content management system.',
            ]
        );

        $this->command->info('About page created');
    }

    private function createContactPage(User $user): void
    {
        [
            // Hero
            [
                'id' => Str::uuid()->toString(),
                'type' => 'hero',
                'settings' => [
                    'title' => 'Get in Touch',
                    'subtitle' => 'Have questions? We would love to hear from you. Send us a message and we will respond as soon as possible.',
                    'bgColor' => '#4f46e5',
                    'padding' => 'py-24',
                    'animation' => 'animate-in fade-in duration-700',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
            // Two Columns - Form and Info
            [
                'id' => Str::uuid()->toString(),
                'type' => 'columns',
                'settings' => [
                    'columns' => 2,
                    'gap' => 'gap-8',
                    'padding' => 'py-20',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
                'children' => [
                    [
                        'id' => Str::uuid()->toString(),
                        'type' => 'contact-form',
                        'settings' => [
                            'title' => 'Send us a Message',
                            'submitText' => 'Send Message',
                            'successMessage' => 'Thank you! We will get back to you soon.',
                            'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                        ],
                    ],
                    [
                        'id' => Str::uuid()->toString(),
                        'type' => 'text',
                        'settings' => [
                            'content' => '<h3>Contact Information</h3><p><strong>Email:</strong> hello@Jejakawan.com</p><p><strong>Phone:</strong> +1 (555) 123-4567</p><p><strong>Address:</strong><br>123 Innovation Street<br>Tech City, TC 12345</p><h4>Office Hours</h4><p>Monday - Friday: 9am - 6pm<br>Saturday - Sunday: Closed</p>',
                            'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                        ],
                    ],
                ],
            ],
            // Map
            [
                'id' => Str::uuid()->toString(),
                'type' => 'map',
                'settings' => [
                    'address' => '123 Innovation Street, Tech City',
                    'zoom' => 15,
                    'height' => '400px',
                    'padding' => 'py-0',
                    'visibility' => ['mobile' => true, 'tablet' => true, 'desktop' => true],
                ],
            ],
        ];

        Content::updateOrCreate(
            ['slug' => 'contact', 'type' => 'page'],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'type' => 'page',
                'status' => 'published',
                'body' => '<h1>Get in Touch</h1><p>Have questions? We would love to hear from you. Send us a message and we will respond as soon as possible.</p>',
                'author_id' => $user->id,
                'published_at' => now(),
                'meta_title' => 'Contact Jejakawan - Get in Touch',
                'meta_description' => 'Have questions about Jejakawan? Contact our team for support, partnerships, or general inquiries.',
            ]
        );

        $this->command->info('Contact page created');
    }

    /**
     * @param  array<string, Category>  $categories
     * @param  list<Tag>  $tags
     */
    private function createBlogPosts(User $user, array $categories, array $tags): void
    {
        $posts = [
            [
                'title' => 'Getting Started with Jejakawan',
                'slug' => 'getting-started',
                'excerpt' => 'Learn how to create stunning content in Jejakawan.',
                'body' => '<p>Jejakawan is a powerful tool that allows you to create beautiful, responsive pages easily.</p>',
                'category' => 'tutorial',
                'is_featured' => true,
            ],
            [
                'title' => 'Best Practices for SEO in Jejakawan',
                'slug' => 'seo-best-practices',
                'excerpt' => 'Discover how to optimize your content for search engines using Jejakawan built-in SEO tools.',
                'body' => '<p>Search engine optimization is crucial for getting your content discovered. Jejakawan comes with powerful SEO tools built right in.</p><h2>Meta Tags</h2><p>Every page and post in Jejakawan can have custom meta titles, descriptions, and keywords. These are essential for search engine rankings.</p><h2>URL Structure</h2><p>Clean, descriptive URLs help both users and search engines understand your content. Jejakawan automatically generates SEO-friendly slugs from your titles.</p><h2>Performance</h2><p>Page speed is a ranking factor. Jejakawan is optimized for performance with lazy loading, code splitting, and efficient caching.</p>',
                'category' => 'technology',
                'is_featured' => true,
            ],
            [
                'title' => 'Designing Beautiful Themes',
                'slug' => 'design-guide',
                'excerpt' => 'A comprehensive guide to creating custom themes in Jejakawan.',
                'body' => '<p>Customizing your site appearance is easy in Jejakawan.</p><h2>Global Styles</h2><p>Set your brand colors, typography, and spacing once and apply them everywhere. Changes propagate throughout your entire site.</p>',
                'category' => 'design',
                'is_featured' => false,
            ],
            [
                'title' => 'Building Multi-language Websites',
                'slug' => 'multilanguage-websites',
                'excerpt' => 'Learn how to create websites that support multiple languages with Jejakawan i18n features.',
                'body' => '<p>Jejakawan has built-in support for internationalization (i18n), making it easy to create multi-language websites.</p><h2>Language Switcher</h2><p>Add a language switcher to your site header to allow visitors to choose their preferred language.</p><h2>Translation Management</h2><p>All interface text can be translated through JSON language files. Add new languages easily by creating new translation files.</p><h2>RTL Support</h2><p>Jejakawan themes support right-to-left languages like Arabic and Hebrew with automatic layout mirroring.</p>',
                'category' => 'tutorial',
                'is_featured' => false,
            ],
            [
                'title' => 'Scaling Your Business with Jejakawan',
                'slug' => 'scaling-business-Jejakawan',
                'excerpt' => 'How Jejakawan can help you scale your online business with powerful content management.',
                'body' => '<p>As your business grows, you need a Jejakawan that can grow with you. Jejakawan is built for scale.</p><h2>Performance at Scale</h2><p>With Redis caching, queue workers, and optimized database queries, Jejakawan handles high traffic with ease.</p><h2>User Management</h2><p>Create teams with different roles and permissions. Control who can publish, edit, or manage content.</p><h2>API-First</h2><p>Jejakawan provides a comprehensive REST API, allowing you to integrate with external services and build headless applications.</p>',
                'category' => 'business',
                'is_featured' => false,
            ],
        ];

        foreach ($posts as $postData) {
            $category = $categories[$postData['category']] ?? null;

            $content = Content::updateOrCreate(
                ['slug' => $postData['slug'], 'type' => 'post'],
                [
                    'title' => $postData['title'],
                    'slug' => $postData['slug'],
                    'type' => 'post',
                    'status' => 'published',
                    'body' => $postData['body'],
                    'excerpt' => $postData['excerpt'],
                    // 'blocks' => [], // Removed obsolete blocks attribute
                    'author_id' => $user->id,
                    'category_id' => $category?->id,
                    'is_featured' => $postData['is_featured'],
                    'published_at' => now()->subDays(random_int(1, 30)),
                    'meta_title' => $postData['title'],
                    'meta_description' => $postData['excerpt'],
                ]
            );

            // Attach random tags
            $randomTags = collect($tags)->random(random_int(2, 4))->pluck('id');
            $content->tags()->sync($randomTags);
        }

        $this->command->info('Created 5 blog posts');
    }

    private function createMediaFolders(User $user): void
    {
        $folders = [
            ['name' => 'Wallpapers', 'slug' => 'wallpapers'],
            ['name' => 'Blog Images', 'slug' => 'blog-images'],
            ['name' => 'UI Assets', 'slug' => 'ui-assets'],
            ['name' => 'Product Documentation', 'slug' => 'docs'],
        ];

        foreach ($folders as $folder) {
            Folder::updateOrCreate(
                ['slug' => $folder['slug']],
                array_merge($folder, ['author_id' => $user->id])
            );
        }

        $this->command->info('Created 4 media folders');
    }

    private function createComments(User $user): void
    {
        $posts = Content::where('type', 'post')->get();
        $sampleComments = [
            'Great article! This really helped me understand the builder.',
            'I love the design of this Jejakawan. Is there a dark mode?',
            'Very helpful tutorial, looking forward to the next one!',
            'Could you elaborate more on the security features?',
            'This is exactly what I was looking for. Simply amazing.',
        ];

        foreach ($posts as $post) {
            // Add 1-3 comments per post
            $count = random_int(1, 3);
            for ($i = 0; $i < $count; $i++) {
                Comment::create([
                    'content_id' => $post->id,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'body' => $sampleComments[array_rand($sampleComments)],
                    'status' => random_int(0, 1) !== 0 ? 'approved' : 'pending',
                ]);
            }
        }

        $this->command->info('Created sample comments');
    }
}
