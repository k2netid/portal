<?php

namespace Modules\Analytics\Services;

use Illuminate\Support\Facades\Log;
use Modules\Analytics\Models\AnalyticsEvent;
use Modules\Analytics\Models\AnalyticsSession;

class AnalyticsService
{
    /**
     * Track a custom event
     *
     * @param  string  $eventType  Type of event (e.g., 'click', 'form_submit', 'download')
     * @param  string  $eventName  Name of the event (e.g., 'Button Click: Subscribe')
     * @param  array<string, mixed>  $data  Additional event data
     * @param  string|null  $contentId  Related content ID (optional)
     */
    public static function trackEvent(string $eventType, string $eventName, array $data = [], ?string $contentId = null): AnalyticsEvent
    {
        try {
            $sessionId = session()->getId();

            // Ensure session exists
            if ($sessionId) {
                AnalyticsSession::start(request(), $sessionId);
            }

            return AnalyticsEvent::track($eventType, $eventName, $data, $contentId);
        } catch (\Exception $e) {
            Log::error('Analytics event tracking failed: '.$e->getMessage(), [
                'event_type' => $eventType,
                'event_name' => $eventName,
            ]);

            // Return a dummy event to prevent breaking the application
            return new AnalyticsEvent;
        }
    }

    /**
     * Track multiple events in batch
     *
     * @param  array<int, array{type?: string, name?: string, data?: array<string, mixed>, content_id?: string|null}>  $events  Array of events [['type' => 'click', 'name' => 'Button', 'data' => []]]
     * @return array<int, AnalyticsEvent>
     */
    public static function trackBatch(array $events): array
    {
        $tracked = [];
        foreach ($events as $event) {
            $tracked[] = self::trackEvent(
                $event['type'] ?? 'custom',
                $event['name'] ?? 'Unknown Event',
                $event['data'] ?? [],
                $event['content_id'] ?? null
            );
        }

        return $tracked;
    }

    /**
     * Track content interaction
     *
     * @param  string  $contentId  Content ID
     * @param  string  $action  Action type (view, like, share, comment)
     * @param  array<string, mixed>  $data  Additional data
     */
    public static function trackContentInteraction(string $contentId, string $action, array $data = []): AnalyticsEvent
    {
        return self::trackEvent(
            'content_interaction',
            ucfirst($action).' Content',
            array_merge($data, ['content_id' => $contentId, 'action' => $action]),
            $contentId
        );
    }

    /**
     * Track form submission
     *
     * @param  string  $formName  Form name/identifier
     * @param  array<string, mixed>  $data  Form data (sanitized)
     */
    public static function trackFormSubmission(string $formName, array $data = []): AnalyticsEvent
    {
        return self::trackEvent(
            'form_submit',
            'Form Submit: '.$formName,
            array_merge($data, ['form_name' => $formName])
        );
    }

    /**
     * Track download
     *
     * @param  string  $fileName  File name
     * @param  string  $fileType  File type/extension
     * @param  string|null  $mediaId  Media ID if applicable
     */
    public static function trackDownload(string $fileName, string $fileType, ?string $mediaId = null): AnalyticsEvent
    {
        return self::trackEvent(
            'download',
            'Download: '.$fileName,
            ['file_name' => $fileName, 'file_type' => $fileType, 'media_id' => $mediaId],
            $mediaId
        );
    }

    /**
     * Track search query
     *
     * @param  string  $query  Search query
     * @param  int  $resultsCount  Number of results
     */
    public static function trackSearch(string $query, int $resultsCount = 0): AnalyticsEvent
    {
        return self::trackEvent(
            'search',
            'Search: '.$query,
            ['query' => $query, 'results_count' => $resultsCount]
        );
    }

    /**
     * Track video play
     *
     * @param  string  $videoTitle  Video title
     * @param  string|null  $contentId  Content ID
     */
    public static function trackVideoPlay(string $videoTitle, ?string $contentId = null): AnalyticsEvent
    {
        return self::trackEvent(
            'video_play',
            'Video Play: '.$videoTitle,
            ['video_title' => $videoTitle],
            $contentId
        );
    }

    /**
     * Track button/link click
     *
     * @param  string  $buttonName  Button/link name
     * @param  string  $url  URL clicked
     */
    public static function trackClick(string $buttonName, string $url): AnalyticsEvent
    {
        return self::trackEvent(
            'click',
            'Click: '.$buttonName,
            ['button_name' => $buttonName, 'url' => $url]
        );
    }
}
