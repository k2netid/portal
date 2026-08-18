<?php

declare(strict_types=1);

namespace Modules\Content\Publishing\Services;

use Modules\Core\System\Models\Setting;

class CommentSecurityService
{
    /**
     * Check if comment content is spam
     */
    public function isSpam(string $content, ?string $authorEmail = null, ?string $authorIp = null): bool
    {
        if ($this->containsBannedWords($content)) {
            return true;
        }

        // Future: Check against IP blocklist or spam APIs (Akismet)
        return $this->exceedsLinkLimit($content);
    }

    /**
     * Check for banned words
     * Check for banned words
     */
    protected function containsBannedWords(string $content): bool
    {
        $bannedWords = Setting::get('comments.security.banned_words', []);

        if (empty($bannedWords)) {
            return false;
        }

        if (is_string($bannedWords)) {
            /** @var array<int, string>|null $decoded */
            $decoded = json_decode($bannedWords, true);
            $bannedWords = $decoded ?? [];
        }

        if (! is_array($bannedWords)) {
            return false;
        }

        foreach ($bannedWords as $word) {
            if (is_string($word) && stripos($content, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check link limit
     */
    protected function exceedsLinkLimit(string $content): bool
    {
        $maxLinksRaw = Setting::get('comments.security.max_links', 2);
        $maxLinks = is_numeric($maxLinksRaw) ? (int) $maxLinksRaw : 2;

        // Count http/https occurrences
        $linkCount = substr_count(strtolower($content), 'http://') +
                     substr_count(strtolower($content), 'https://');

        return $linkCount > $maxLinks;
    }

    /**
     * Determine initial status based on moderation settings
     */
    public function getInitialStatus(bool $isSpam): string
    {
        if ($isSpam) {
            return 'spam';
        }

        $moderationEnabled = Setting::get('comments.security.moderation_enabled', true);

        return $moderationEnabled ? 'pending' : 'approved';
    }
}
