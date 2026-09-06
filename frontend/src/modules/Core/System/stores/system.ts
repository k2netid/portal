import { persistConsoleDashboardSlug, readConsoleDashboardSlugFromPayload } from '@/config/console';
import { persistConsoleDarkModeToStorage, readConsoleDarkModeFromStorage } from '@/config/theme';
import { logger } from '@/shared/utils/logger';
import { defineStore } from 'pinia';
import api, { type ApiRequestConfig } from '@/engine/api/client';
import { applyFavicon, isGenericEngineFavicon } from '@/modules/Core/System/utils/favicon';


export interface SiteSettings {
    site_name: string;
    site_description: string;
    site_url: string;
    admin_email: string;
    site_version: string;
    site_logo: string;
    site_favicon: string;
    [key: string]: unknown;
}

export interface SystemState {
    settings: Record<string, unknown>;
    appIdentity: {
        app_name: string;
        app_logo: string;
        app_favicon: string;
        app_license_tier: string;
        has_white_label: boolean;
    };
    siteSettings: SiteSettings;
    maintenance: {
        mode: boolean;
        title: string;
        message: string;
        countdown_enabled: boolean;
        end_time: string;
    };
    loadingGroups: Record<string, boolean>;
    settingsPromises: Record<string, Promise<unknown>>;
    publicSettingsLoaded: boolean;
    publicSettingsPromise: Promise<unknown> | null;
    themeMode: 'light' | 'dark' | 'system';
    isDarkMode: boolean;
    consoleDashboardSlug: string;
    activeExtensions: string[];
}

// siteSettings moved back to CmsStore as per user request (Jejakawan is public web authority)

export const useSystemStore = defineStore('system', {
    state: (): SystemState => ({
        settings: {},
        appIdentity: {
            app_name: 'Jejakawan',
            app_logo: '',
            app_favicon: '',
            app_license_tier: 'basic',
            has_white_label: false,
        },
        siteSettings: {
            site_name: 'Jejakawan',
            site_description: '',
            site_url: '',
            admin_email: '',
            site_version: '',
            site_logo: '',
            site_favicon: '',
        },
        maintenance: {
            mode: false,
            title: '',
            message: '',
            countdown_enabled: false,
            end_time: '',
        },
        loadingGroups: {},
        settingsPromises: {},
        publicSettingsLoaded: false,
        publicSettingsPromise: null,
        themeMode: 'system', // 'light', 'dark', 'system'
        isDarkMode: false,
        consoleDashboardSlug: 'dash',
        activeExtensions: [],
    }),

    actions: {
        async fetchSettingsGroup(group: string) {
            // If already loading, return existing promise
            if (this.loadingGroups[group]) {
                return this.settingsPromises[group];
            }

            // Mark this group as loading
            this.loadingGroups = { ...this.loadingGroups, [group]: true };

            // Create and store the promise for this fetch operation
            const promise = (async () => {
                try {
                    // Note: Calling System settings endpoint (canonical)
                    const response = await api.get(`/manage/system/settings/group/${group}`);
                    const settingsData = response.data || {};
                    
                    // Only update if there are actual new keys or changed values
                    const hasChanges = Object.entries(settingsData).some(([key, value]) => this.settings[key] !== value);
                    if (hasChanges) {
                        this.settings = { ...this.settings, ...settingsData };
                    }
                    return settingsData;
                }
                catch (error: unknown) {
                    logger.error(`[System Store] Error fetching ${group} settings:`, error);
                    return {};
                } finally {
                    this.loadingGroups = { ...this.loadingGroups, [group]: false };
                    delete this.settingsPromises[group];
                }
            })();

            this.settingsPromises = { ...this.settingsPromises, [group]: promise };
            return promise;
        },
        
        async fetchPublicSettings(options: { force?: boolean } = {}) {
            if (this.publicSettingsLoaded && !options.force) {
                return {};
            }
            // If already loading, return existing promise
            if (this.publicSettingsPromise && !options.force) {
                return this.publicSettingsPromise;
            }

            this.publicSettingsPromise = (async () => {
                try {
                    const response = await api.get('/public/system/settings');
                    const data = response.data || {};
                    
                    // Sync Site Settings (including public security knobs used by console + member portal)
                    this.siteSettings = {
                        ...this.siteSettings,
                        site_name: data.site_name || this.siteSettings.site_name,
                        site_description: data.site_description || '',
                        site_url: data.site_url || '',
                        admin_email: data.admin_email || '',
                        site_version: data.site_version || '',
                        site_logo: data.site_logo || '',
                        site_favicon: isGenericEngineFavicon(String(data.site_favicon || ''))
                            ? ''
                            : String(data.site_favicon || ''),
                        enable_registration: data.enable_registration,
                        enable_member_registration: data.enable_member_registration,
                        require_email_verification: data.require_email_verification,
                        enable_2fa: data.enable_2fa,
                        password_policy: data.password_policy ?? this.siteSettings.password_policy,
                    };

                    // Bridge siteSettings into settings dictionary for public themes
                    this.settings = {
                        ...this.settings,
                        ...this.siteSettings,
                        ...data,
                    };

                    // Sync App Identity (Branding)
                    const licenseTier = String(data.app_license_tier || data.license_type || this.appIdentity.app_license_tier || 'community').toLowerCase();
                    const hasWhiteLabel = typeof data.has_white_label === 'boolean'
                        ? data.has_white_label
                        : ['enterprise', 'white_label', 'pro_plus'].includes(licenseTier);

                    const syncEnabled = Boolean(data.brand_sync_site_identity);
                    const brandLogo = (data.brand_logo as string) || '';
                    const brandFavicon = (data.brand_favicon as string) || '';

                    // In console, brand overrides apply if White Label is active.
                    // Fallback is strictly Jejakawan Core (/logo.png & /favicon.ico) unless syncEnabled is true.
                    const effectiveAppLogo = hasWhiteLabel
                        ? (brandLogo || (syncEnabled ? data.site_logo : '') || '/logo.png')
                        : '/logo.png';
                    const effectiveAppFavicon = hasWhiteLabel
                        ? (brandFavicon || (syncEnabled ? data.site_favicon : '') || '/favicon.ico')
                        : '/favicon.ico';

                    this.appIdentity = {
                        ...this.appIdentity,
                        app_name: (hasWhiteLabel ? (data.app_name || (syncEnabled ? data.site_name : '')) : '') || 'Jejakawan',
                        app_logo: effectiveAppLogo,
                        app_favicon: effectiveAppFavicon,
                        app_license_tier: licenseTier,
                        has_white_label: hasWhiteLabel,
                    };

                    this.settings = {
                        ...this.settings,
                        ...this.siteSettings,
                        ...data,
                        brand_logo: brandLogo,
                        brand_favicon: brandFavicon,
                        brand_sync_site_identity: syncEnabled,
                        branding_display: data.branding_display || 'both',
                    };

                    this.maintenance = {
                        mode: !!data.maintenance_mode,
                        title: data.maintenance_title || '',
                        message: data.maintenance_message || '',
                        countdown_enabled: !!data.maintenance_countdown_enabled,
                        end_time: data.maintenance_end_time || '',
                    };
                    
                    this.consoleDashboardSlug = readConsoleDashboardSlugFromPayload(data);
                    persistConsoleDashboardSlug(this.consoleDashboardSlug);
                    
                    if (Array.isArray(data.active_extensions)) {
                        this.activeExtensions = data.active_extensions;
                    }

                    applyFavicon(effectiveAppFavicon, { allowGeneric: true });

                    return data;
                } catch (error) {
                    logger.error('[System Store] Error fetching public settings:', error);
                    return {};
                } finally {
                    this.publicSettingsLoaded = true;
                    this.publicSettingsPromise = null;
                }
            })();

            return this.publicSettingsPromise;
        },

        async fetchAppIdentity() {
            try {
                // Fetch branding from both System & Brand settings groups
                const [systemRes, brandRes] = await Promise.all([
                    api.get('/manage/system/settings/group/system').catch(() => ({ data: {} })),
                    api.get('/manage/system/settings/group/brand').catch(() => ({ data: {} })),
                ]);
                const systemData = (systemRes.data?.data ?? systemRes.data) || {};
                const brandData = (brandRes.data?.data ?? brandRes.data) || {};
                const rawData = { ...systemData, ...brandData };

                const licenseTier = String(rawData.app_license_tier || rawData.license_type || this.appIdentity.app_license_tier || 'community').toLowerCase();
                const hasWhiteLabel = typeof rawData.has_white_label === 'boolean'
                    ? rawData.has_white_label
                    : ['enterprise', 'white_label', 'pro_plus'].includes(licenseTier);

                const syncEnabled = Boolean(brandData.brand_sync_site_identity);
                const brandLogo = (brandData.brand_logo as string) || '';
                const brandFavicon = (brandData.brand_favicon as string) || '';
                const appLogo = (brandData.app_logo as string) || (brandData.app_logo_light as string) || '';
                const appFavicon = (brandData.app_favicon as string) || '';

                const effectiveAppLogo = hasWhiteLabel
                    ? (brandLogo || appLogo || (syncEnabled ? this.siteSettings.site_logo : '') || '/logo.png')
                    : '/logo.png';
                const effectiveAppFavicon = hasWhiteLabel
                    ? (brandFavicon || appFavicon || (syncEnabled ? this.siteSettings.site_favicon : '') || '/favicon.ico')
                    : '/favicon.ico';

                this.appIdentity = {
                    ...this.appIdentity,
                    app_name: (hasWhiteLabel ? (brandData.app_name || (syncEnabled ? this.siteSettings.site_name : '')) : '') || 'Jejakawan',
                    app_logo: effectiveAppLogo,
                    app_favicon: effectiveAppFavicon,
                    app_license_tier: licenseTier,
                    has_white_label: hasWhiteLabel,
                };

                this.settings = {
                    ...this.settings,
                    ...brandData,
                    brand_logo: brandLogo,
                    brand_favicon: brandFavicon,
                    brand_sync_site_identity: syncEnabled,
                    branding_display: brandData.branding_display || 'both',
                };

                applyFavicon(effectiveAppFavicon, { allowGeneric: true });

                return this.appIdentity;
            } catch (error) {
                logger.error('[System Store] Error fetching app identity:', error);
                return this.appIdentity;
            }
        },

        getSetting(key: string, defaultValue: unknown = null) {
            return this.settings[key] !== undefined ? this.settings[key] : defaultValue;
        },

        isAuthenticatedLocally(): boolean {
            const userRaw = localStorage.getItem('user');
            if (!userRaw) return false;

            try {
                const parsed = JSON.parse(userRaw);
                return !!parsed && typeof parsed === 'object';
            } catch {
                return false;
            }
        },

        async initTheme() {
            // 1. Detect system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            // 2. Load from localStorage (`console-dark-mode`) or follow OS preference
            const saved = readConsoleDarkModeFromStorage();

            this.themeMode = saved as 'light' | 'dark' | 'system';

            // 3. Resolve actual dark mode state
            this.isDarkMode = saved === 'dark' || (saved === 'system' && prefersDark);

            // 4. Apply to document
            this.applyThemeToDocument();

            // 5. Watch for system changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (this.themeMode === 'system') {
                    this.isDarkMode = e.matches;
                    this.applyThemeToDocument();
                }
            });

            // 6. Try to load from backend (if authenticated)
            await this.loadThemePreferences();
        },

        async loadThemePreferences() {
            if (!this.isAuthenticatedLocally()) return;
            try {
                const response = await api.get('/manage/system/profile/preferences', { _skipManualRedirect: true } as ApiRequestConfig);
                const backendMode = response.data?.dark_mode;
                const isValidThemeMode = backendMode === 'light' || backendMode === 'dark' || backendMode === 'system';
                if (isValidThemeMode) {
                    if (this.themeMode !== backendMode) {
                        this.setThemeMode(backendMode, false); // Don't sync back to backend
                    }
                }
            } catch (error: unknown) {
                const message = error instanceof Error ? error.message : String(error);
                logger.debug('[System Store] Failed to load theme preferences:', { message });
            }
        },

        async syncThemeWithBackend(mode: string) {
            if (!this.isAuthenticatedLocally()) return;
            try {
                await api.put('/manage/system/profile/preferences', { dark_mode: mode }, { _skipManualRedirect: true } as ApiRequestConfig);
            } catch (error: unknown) {
                const message = error instanceof Error ? error.message : String(error);
                logger.debug('[System Store] Theme sync failed:', { message });
            }
        },

        setThemeMode(mode: 'light' | 'dark' | 'system', syncToBackend = true) {
            // Add no-transitions class to prevent flashing
            document.documentElement.classList.add('no-transitions');

            this.themeMode = mode;
            persistConsoleDarkModeToStorage(mode);

            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            this.isDarkMode = mode === 'dark' || (mode === 'system' && prefersDark);

            this.applyThemeToDocument();

            if (syncToBackend) {
                this.syncThemeWithBackend(mode);
            }

            // Remove no-transitions class after short delay
            setTimeout(() => {
                if (typeof document !== 'undefined') {
                    document.documentElement.classList.remove('no-transitions');
                }
            }, 50);
        },

        toggleDarkMode(value?: boolean) {
            // If value is provided (e.g. from a switch), use it. 
            // Otherwise, toggle current state.
            const isDark = value !== undefined ? value : !this.isDarkMode;
            const next = isDark ? 'dark' : 'light';
            this.setThemeMode(next);
        },

        applyThemeToDocument() {
            if (this.isDarkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },
    },
});
