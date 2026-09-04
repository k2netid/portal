<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\System\Models\Extension;

class InstagramFeedService
{
    private const GRAPH_API_BASE = 'https://graph.instagram.com';

    /**
     * Test connection to Meta Instagram Graph API with provided credentials.
     *
     * @return array{success: bool, account?: array<string, mixed>, error?: string}
     */
    public function testConnection(string $token, string $username = ''): array
    {
        $token = trim($token);
        if ($token === '') {
            return [
                'success' => false,
                'error' => 'Access token cannot be empty.',
            ];
        }

        // Support mock testing in dev/test environments
        if (str_starts_with($token, 'mock_') || str_starts_with($token, 'test_')) {
            return [
                'success' => true,
                'account' => [
                    'id' => 'mock_ig_account_12345678',
                    'username' => $username !== '' ? ltrim($username, '@') : 'instagram_creator',
                    'account_type' => 'BUSINESS',
                    'media_count' => 24,
                ],
            ];
        }

        try {
            $response = Http::timeout(10)->get(self::GRAPH_API_BASE.'/me', [
                'fields' => 'id,username,account_type,media_count',
                'access_token' => $token,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'account' => [
                        'id' => (string) ($data['id'] ?? ''),
                        'username' => (string) ($data['username'] ?? $username),
                        'account_type' => (string) ($data['account_type'] ?? 'CREATOR'),
                        'media_count' => (int) ($data['media_count'] ?? 0),
                    ],
                ];
            }

            $error = $response->json('error.message') ?? 'HTTP '.$response->status().' from Instagram Graph API.';

            return [
                'success' => false,
                'error' => $error,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Connection failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get public feed data.
     * Guaranteed fail-safe: returns empty payload if inactive, unconfigured, or invalid.
     *
     * @return array{enabled: bool, reason?: string, username?: string, account_id?: string, items: list<array<string, mixed>>}
     */
    public function getPublicFeed(): array
    {
        /** @var Extension|null $extension */
        $extension = Extension::query()
            ->where('slug', 'instagram-feed')
            ->where('status', 'active')
            ->first();

        if ($extension === null) {
            return [
                'enabled' => false,
                'reason' => 'inactive',
                'items' => [],
            ];
        }

        $settings = is_array($extension->settings) ? $extension->settings : [];
        $token = trim((string) ($settings['access_token'] ?? ''));
        $username = trim((string) ($settings['instagram_username'] ?? ''));

        if ($token === '') {
            return [
                'enabled' => false,
                'reason' => 'unconfigured',
                'items' => [],
            ];
        }

        $ttlMinutes = (int) ($settings['cache_ttl_minutes'] ?? 60);
        if ($ttlMinutes < 5) {
            $ttlMinutes = 5;
        }

        $postLimit = (int) ($settings['post_limit'] ?? 8);
        if ($postLimit < 1 || $postLimit > 24) {
            $postLimit = 8;
        }

        $showLikes = (bool) ($settings['show_likes_count'] ?? true);
        $showComments = (bool) ($settings['show_comments_count'] ?? true);
        $filterKeywords = (string) ($settings['comment_filter_keywords'] ?? '');

        $cacheKey = 'instagram_feed_posts_'.md5($username.$token.$postLimit);

        try {
            $feed = Cache::remember($cacheKey, now()->addMinutes($ttlMinutes), function () use ($token, $username, $postLimit, $showLikes, $showComments, $filterKeywords) {
                return $this->fetchFromInstagram($token, $username, $postLimit, $showLikes, $showComments, $filterKeywords);
            });

            return [
                'enabled' => true,
                'username' => ltrim($username, '@'),
                'account_id' => (string) ($settings['instagram_account_id'] ?? ''),
                'items' => is_array($feed) ? $feed : [],
            ];
        } catch (Exception $e) {
            Log::warning('[InstagramFeedService] Public feed fetch error: '.$e->getMessage());

            return [
                'enabled' => false,
                'reason' => 'fetch_error',
                'items' => [],
            ];
        }
    }

    /**
     * Fetch media and comments from Instagram Graph API.
     *
     * @return list<array<string, mixed>>
     */
    private function fetchFromInstagram(
        string $token,
        string $username,
        int $limit,
        bool $showLikes,
        bool $showComments,
        string $filterKeywords,
    ): array {
        // Mock payload for testing
        if (str_starts_with($token, 'mock_') || str_starts_with($token, 'test_')) {
            return $this->generateMockFeed($username, $limit);
        }

        $response = Http::timeout(15)->get(self::GRAPH_API_BASE.'/me/media', [
            'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,like_count,comments_count',
            'limit' => $limit,
            'access_token' => $token,
        ]);

        if (! $response->successful()) {
            throw new Exception('Instagram Graph API error: '.($response->json('error.message') ?? 'HTTP '.$response->status()));
        }

        $rawItems = $response->json('data') ?? [];
        if (! is_array($rawItems)) {
            return [];
        }

        $items = [];
        $keywords = explode(',', strtolower($filterKeywords));
        $trimmedKeywords = array_map('trim', $keywords);
        $filterList = array_values(array_filter($trimmedKeywords));

        foreach ($rawItems as $item) {
            if (! is_array($item)) {
                continue;
            }

            $mediaId = (string) ($item['id'] ?? '');
            $mediaType = (string) ($item['media_type'] ?? 'IMAGE');
            $mediaUrl = (string) ($item['media_url'] ?? '');
            $thumbnailUrl = (string) ($item['thumbnail_url'] ?? $mediaUrl);
            $caption = (string) ($item['caption'] ?? '');
            $permalink = (string) ($item['permalink'] ?? "https://instagram.com/p/{$mediaId}");
            $likeCount = (int) ($item['like_count'] ?? 0);
            $commentsCount = (int) ($item['comments_count'] ?? 0);
            $timestamp = (string) ($item['timestamp'] ?? now()->toIso8601String());

            $comments = [];
            if ($showComments && $mediaId !== '') {
                $comments = $this->fetchTopComments($mediaId, $token, $filterList);
            }

            $items[] = [
                'id' => $mediaId,
                'media_type' => $mediaType,
                'media_url' => $mediaUrl,
                'thumbnail_url' => $thumbnailUrl,
                'caption' => $caption,
                'permalink' => $permalink,
                'like_count' => $showLikes ? $likeCount : null,
                'comments_count' => $showComments ? $commentsCount : null,
                'timestamp' => $timestamp,
                'comments' => $comments,
            ];
        }

        return $items;
    }

    /**
     * Fetch top comments for a given media post.
     *
     * @param  list<string>  $filterKeywords
     * @return list<array{id: string, username: string, text: string, timestamp: string}>
     */
    private function fetchTopComments(string $mediaId, string $token, array $filterKeywords): array
    {
        try {
            $res = Http::timeout(8)->get(self::GRAPH_API_BASE."/{$mediaId}/comments", [
                'fields' => 'id,text,username,timestamp',
                'limit' => 5,
                'access_token' => $token,
            ]);

            if (! $res->successful()) {
                return [];
            }

            $data = $res->json('data') ?? [];
            if (! is_array($data)) {
                return [];
            }

            $comments = [];
            foreach ($data as $c) {
                if (! is_array($c)) {
                    continue;
                }

                $text = (string) ($c['text'] ?? '');
                $lower = strtolower($text);

                // Filter keywords
                $blocked = false;
                foreach ($filterKeywords as $kw) {
                    if ($kw !== '' && str_contains($lower, $kw)) {
                        $blocked = true;
                        break;
                    }
                }

                if ($blocked) {
                    continue;
                }

                $comments[] = [
                    'id' => (string) ($c['id'] ?? ''),
                    'username' => (string) ($c['username'] ?? 'user'),
                    'text' => $text,
                    'timestamp' => (string) ($c['timestamp'] ?? ''),
                ];
            }

            return $comments;
        } catch (Exception) {
            return [];
        }
    }

    /**
     * Generate structured mock feed for development, testing, and preview scenarios.
     *
     * @return list<array<string, mixed>>
     */
    private function generateMockFeed(string $username, int $limit): array
    {
        $username = $username !== '' ? ltrim($username, '@') : 'portal';
        $items = [];

        $mockCaptions = [
            'Semangat berinovasi dan berkarya! Dokumentasi kegiatan praktik kejuruan dan kolaborasi industri.',
            'Penyerahan piagam apresiasi dan medali juara LKS tingkat provinsi. Selamat untuk seluruh perwakilan siswa!',
            'Suasana hangat dan penuh antusiasme workshop persiapan karier dan literasi digital bersama mitra industri.',
            'Karya kreatif siswa kejuruan dalam pameran teknologi & desain. Bangga dengan talenta muda masa depan!',
            'Kegiatan apel pagi dan pembinaan karakter di lapangan utama. Membangun integritas, disiplin, dan kepedulian.',
            'Kunjungan industri dan penandatanganan MoU kemitraan strategis untuk program magang dan penyerapan lulusan.',
            'Gelar aksi peduli lingkungan dan penanaman pohon di area kampus hijau. Langkah kecil untuk dampak besar.',
            'Sorotan kegiatan ekstrakurikuler robotik, seni budaya, dan olahraga pekan ini. Terus asah potensi terbaikmu!',
        ];

        for ($i = 1; $i <= min($limit, count($mockCaptions)); $i++) {
            $items[] = [
                'id' => "mock_post_{$i}",
                'media_type' => $i % 3 === 0 ? 'CAROUSEL_ALBUM' : ($i % 2 === 0 ? 'VIDEO' : 'IMAGE'),
                'media_url' => "https://picsum.photos/seed/igpost{$i}/800/800",
                'thumbnail_url' => "https://picsum.photos/seed/igpost{$i}/800/800",
                'caption' => $mockCaptions[$i - 1],
                'permalink' => "https://instagram.com/{$username}",
                'like_count' => 140 + ($i * 27),
                'comments_count' => 12 + ($i * 3),
                'timestamp' => now()->subHours($i * 6)->toIso8601String(),
                'comments' => [
                    [
                        'id' => "comm_{$i}_1",
                        'username' => 'alumni_sukses',
                        'text' => 'Keren banget! Bangga selalu dengan almamater.',
                        'timestamp' => now()->subHours($i * 5)->toIso8601String(),
                    ],
                    [
                        'id' => "comm_{$i}_2",
                        'username' => 'mitra_industri',
                        'text' => 'Apresiasi tinggi untuk komitmen dan kualitas karya siswanya!',
                        'timestamp' => now()->subHours($i * 4)->toIso8601String(),
                    ],
                ],
            ];
        }

        return $items;
    }
}
