import { describe, expect, it } from 'vitest';
import {
    CONSOLE_DASHBOARD_SLUG_KEY,
    collectConsoleDashboardSlugCandidates,
    pathUsesConsoleDashboardSlug,
    readConsoleDashboardSlugFromPayload,
    resolveConsoleDashboardLocation,
} from '@/config/console';

describe('console dashboard slug', () => {
    it('reads new key from API payload', () => {
        expect(readConsoleDashboardSlugFromPayload({
            [CONSOLE_DASHBOARD_SLUG_KEY]: 'ja-dash',
        })).toBe('ja-dash');
    });

    it('prefers console_dashboard_slug when both keys exist', () => {
        expect(readConsoleDashboardSlugFromPayload({
            [CONSOLE_DASHBOARD_SLUG_KEY]: 'ja-dash',
            admin_dashboard_slug: 'old-dash',
        })).toBe('ja-dash');
    });

    it('ignores removed admin_dashboard_slug payload key', () => {
        expect(readConsoleDashboardSlugFromPayload({
            admin_dashboard_slug: 'legacy-dash',
        })).toBe('dash');
    });

    it('defaults to dash when missing', () => {
        expect(readConsoleDashboardSlugFromPayload({})).toBe('dash');
    });

    it('recognizes ja-dash console paths', () => {
        expect(collectConsoleDashboardSlugCandidates()).toContain('ja-dash');
        expect(pathUsesConsoleDashboardSlug('/ja-dash/platform', 'ja-dash')).toBe(true);
    });

    it('resolves dashboard landing as slug path (no named route params)', () => {
        expect(resolveConsoleDashboardLocation('ja-dash')).toEqual({
            path: '/ja-dash/dashboard',
        });
    });
});
