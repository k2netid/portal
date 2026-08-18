<?php

namespace Modules\Content\Publishing\Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Content\Publishing\Services\CommentSecurityService;
use Modules\Core\System\Models\Setting;
use Tests\TestCase;

class CommentSecurityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentSecurityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CommentSecurityService;
    }

    public function test_is_spam_with_banned_words(): void
    {
        Setting::set('comments.security.banned_words', ['spam', 'badword'], 'json');

        $this->assertTrue($this->service->isSpam('This is a spam message'));
        $this->assertFalse($this->service->isSpam('This is a clean message'));
    }

    public function test_is_spam_banned_words_as_string(): void
    {
        Setting::create([
            'key' => 'comments.security.banned_words',
            'value' => json_encode(['exclusive']),
            'type' => 'string',
        ]);

        $this->assertTrue($this->service->isSpam('Exclusive offer!'));
    }

    public function test_is_spam_exceeds_link_limit(): void
    {
        Setting::set('comments.security.max_links', 1, 'integer');

        $content = 'Check this http://example.com and http://test.com';
        $this->assertTrue($this->service->isSpam($content));

        $clean = 'Check this http://example.com';
        $this->assertFalse($this->service->isSpam($clean));
    }

    public function test_get_initial_status(): void
    {
        // Case 1: Is Spam
        $this->assertEquals('spam', $this->service->getInitialStatus(true));

        // Case 2: Not spam, moderation enabled
        Setting::set('comments.security.moderation_enabled', true, 'boolean');
        $this->assertEquals('pending', $this->service->getInitialStatus(false));

        // Case 3: Not spam, moderation disabled
        Setting::set('comments.security.moderation_enabled', false, 'boolean');
        $this->assertEquals('approved', $this->service->getInitialStatus(false));
    }
}
