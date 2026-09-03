import { describe, it, expect, vi, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import api from '@/engine/api/client';

vi.mock('@/engine/api/client', () => ({
    default: {
        get: vi.fn(),
        put: vi.fn(),
        post: vi.fn(),
    },
}));

vi.mock('@/shared/utils/logger', () => ({
    logger: {
        error: vi.fn(),
        debug: vi.fn(),
        info: vi.fn(),
        warn: vi.fn(),
    },
}));

describe('System Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
        localStorage.clear();
        sessionStorage.clear();
        document.documentElement.className = '';

        // Mock window.matchMedia
        Object.defineProperty(window, 'matchMedia', {
            writable: true,
            value: vi.fn().mockImplementation((query) => ({
                matches: false,
                media: query,
                onchange: null,
                addListener: vi.fn(),
                removeListener: vi.fn(),
                addEventListener: vi.fn(),
                removeEventListener: vi.fn(),
                dispatchEvent: vi.fn(),
            })),
        });
    });

    it('initializes with default system state', () => {
        const store = useSystemStore();
        expect(store.appIdentity.app_name).toBe('Jejakawan');
        expect(store.siteSettings.site_name).toBe('Jejakawan');
        expect(store.maintenance.mode).toBe(false);
        expect(store.publicSettingsLoaded).toBe(false);
        expect(store.themeMode).toBe('system');
        expect(store.isDarkMode).toBe(false);
        expect(store.consoleDashboardSlug).toBe('dash');
    });

    it('fetches settings group and handles deduplication', async () => {
        const store = useSystemStore();
        vi.mocked(api.get).mockResolvedValueOnce({
            data: { site_name: 'Custom Site', custom_key: 'value1' },
        });

        const p1 = store.fetchSettingsGroup('general');
        const p2 = store.fetchSettingsGroup('general');

        const [res1, res2] = await Promise.all([p1, p2]);
        expect(res1).toEqual({ site_name: 'Custom Site', custom_key: 'value1' });
        expect(res2).toEqual({ site_name: 'Custom Site', custom_key: 'value1' });
        expect(api.get).toHaveBeenCalledTimes(1); // deduplicated!
        expect(store.settings.custom_key).toBe('value1');
        expect(store.getSetting('custom_key')).toBe('value1');
        expect(store.getSetting('nonexistent', 'default_val')).toBe('default_val');
    });

    it('handles settings group fetch error gracefully', async () => {
        const store = useSystemStore();
        vi.mocked(api.get).mockRejectedValueOnce(new Error('Network failure'));

        const result = await store.fetchSettingsGroup('broken');
        expect(result).toEqual({});
        expect(store.loadingGroups.broken).toBe(false);
    });

    it('fetches public settings and updates siteSettings and branding', async () => {
        const store = useSystemStore();
        vi.mocked(api.get).mockResolvedValueOnce({
            data: {
                site_name: 'K2NET Portal',
                site_description: 'ISP & MSP Bandung',
                site_url: 'https://staging.k2net.id',
                admin_email: 'admin@k2net.id',
                site_version: '1.0.0',
                maintenance_mode: true,
                maintenance_title: 'Server Maintenance',
                maintenance_message: 'Upgrading network core',
                maintenance_countdown_enabled: true,
                maintenance_end_time: '2026-09-03T12:00:00Z',
                console_dashboard_slug: 'ja-dash',
                active_extensions: ['mail', 'cms'],
                app_license_tier: 'pro_plus',
            },
        });

        await store.fetchPublicSettings();
        expect(store.publicSettingsLoaded).toBe(true);
        expect(store.siteSettings.site_name).toBe('K2NET Portal');
        expect(store.siteSettings.site_url).toBe('https://staging.k2net.id');
        expect(store.maintenance.mode).toBe(true);
        expect(store.maintenance.title).toBe('Server Maintenance');
        expect(store.consoleDashboardSlug).toBe('ja-dash');
        expect(store.activeExtensions).toContain('mail');
        expect(store.appIdentity.has_white_label).toBe(true);

        // Subsequent call without force should return {} immediately
        const cached = await store.fetchPublicSettings();
        expect(cached).toEqual({});
    });

    it('fetches app identity from group system', async () => {
        const store = useSystemStore();
        vi.mocked(api.get).mockResolvedValueOnce({
            data: {
                app_name: 'K2NET Identity',
                app_logo: '/media/logo.png',
                app_license_tier: 'white_label',
            },
        });

        const identity = await store.fetchAppIdentity();
        expect(identity.app_name).toBe('K2NET Identity');
        expect(identity.has_white_label).toBe(true);
        expect(identity.app_logo).toBe('/media/logo.png');
    });

    it('checks isAuthenticatedLocally with valid and invalid local storage', () => {
        const store = useSystemStore();
        expect(store.isAuthenticatedLocally()).toBe(false);

        localStorage.setItem('user', 'invalid-json');
        expect(store.isAuthenticatedLocally()).toBe(false);

        localStorage.setItem('user', JSON.stringify({ id: 1, name: 'Admin' }));
        expect(store.isAuthenticatedLocally()).toBe(true);
    });

    it('manages theme modes, toggles dark mode, and applies class to document', async () => {
        const store = useSystemStore();

        // 1. Light mode
        store.setThemeMode('light', false);
        expect(store.themeMode).toBe('light');
        expect(store.isDarkMode).toBe(false);
        expect(document.documentElement.classList.contains('dark')).toBe(false);

        // 2. Dark mode
        store.setThemeMode('dark', false);
        expect(store.themeMode).toBe('dark');
        expect(store.isDarkMode).toBe(true);
        expect(document.documentElement.classList.contains('dark')).toBe(true);

        // 3. Toggle dark mode
        store.toggleDarkMode();
        expect(store.isDarkMode).toBe(false);
        expect(store.themeMode).toBe('light');

        store.toggleDarkMode(true);
        expect(store.isDarkMode).toBe(true);
        expect(store.themeMode).toBe('dark');
    });

    it('syncs theme mode with backend when authenticated', async () => {
        localStorage.setItem('user', JSON.stringify({ id: 1 }));
        const store = useSystemStore();

        vi.mocked(api.put).mockResolvedValueOnce({ data: { success: true } });
        store.setThemeMode('dark', true);
        expect(api.put).toHaveBeenCalledWith(
            '/manage/system/profile/preferences',
            { dark_mode: 'dark' },
            expect.anything(),
        );
    });

    it('loads theme preferences from backend when authenticated', async () => {
        localStorage.setItem('user', JSON.stringify({ id: 1 }));
        const store = useSystemStore();

        vi.mocked(api.get).mockResolvedValueOnce({
            data: { dark_mode: 'dark' },
        });

        await store.loadThemePreferences();
        expect(store.themeMode).toBe('dark');
        expect(store.isDarkMode).toBe(true);
    });
});
