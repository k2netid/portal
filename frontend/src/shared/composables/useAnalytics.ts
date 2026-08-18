import { logger } from '@/shared/utils/logger';
import { AnalyticsService } from '@/shared/services/analyticsService';

/**
 * Analytics tracking composable for frontend event tracking
 */
export function useAnalytics() {
    const trackEvent = async (eventType: string, eventName: string, data: Record<string, unknown> = {}, contentId: string | number | null = null): Promise<void> => {
        try {
            await AnalyticsService.trackEvent({
                event_type: eventType,
                event_name: eventName,
                event_data: data,
                content_id: contentId,
            });
        } catch (e) {
            logger.warning('Analytics tracking failed:', e);
        }
    };

    const trackClick = (buttonName: string, url: string | null = null) => {
        return trackEvent('click', `Click: ${buttonName}`, {
            button_name: buttonName,
            url: url || window.location.href,
        });
    };

    const trackDownload = (fileName: string, fileType: string | null = null, mediaId: string | number | null = null) => {
        return trackEvent('download', `Download: ${fileName}`, {
            file_name: fileName,
            file_type: fileType,
            media_id: mediaId,
        });
    };

    const trackFormSubmit = (formName: string, data: Record<string, unknown> = {}) => {
        return trackEvent('form_submit', `Form Submit: ${formName}`, {
            form_name: formName,
            ...data,
        });
    };

    const trackSearch = (query: string, resultsCount: number = 0) => {
        return trackEvent('search', `Search: ${query}`, {
            query: query,
            results_count: resultsCount,
        });
    };

    const trackPageView = (pageName: string, url: string | null = null) => {
        return trackEvent('page_view', `Page View: ${pageName}`, {
            page_name: pageName,
            url: url || window.location.href,
        });
    };

    const trackBatch = async (events: Array<{ type: string; name: string; data: unknown; content_id?: string | number }>): Promise<void> => {
        if (!events || events.length === 0) return;

        try {
            await AnalyticsService.trackBatch(events);
        } catch (e) {
            logger.warning('Analytics batch tracking failed:', e);
        }
    };

    return {
        trackEvent,
        trackClick,
        trackDownload,
        trackFormSubmit,
        trackSearch,
        trackPageView,
        trackBatch,
    };
}
