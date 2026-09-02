import { persistConsoleDashboardSlug, readConsoleDashboardSlugFromPayload } from '@/config/console';
import { persistConsoleDarkModeToStorage, readConsoleDarkModeFromStorage } from '@/config/theme';
import { logger } from '@/shared/utils/logger';
import { defineStore } from 'pinia';
import api, { type ApiRequestConfig } from '@/engine/api/client';
import { applyFavicon, isGenericEngineFavicon, resolveFavicon } from '@/modules/Core/System/utils/favicon';


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

                    // Sync App Identity (Branding)
                    // We preserve existing values if the new ones are empty to avoid flickering
                    this.appIdentity = {
                        ...this.appIdentity,
                        app_name: data.app_name || data.site_name || this.appIdentity.app_name || 'Jejakawan',
                        app_logo: data.app_logo || data.site_logo || this.appIdentity.app_logo || '',
                        app_favicon: String(
                            (!isGenericEngineFavicon(String(data.app_favicon || '')) && data.app_favicon)
                            || (!isGenericEngineFavicon(String(data.site_favicon || '')) && data.site_favicon)
                            || this.appIdentity.app_favicon
                            || '',
                        ),
                        app_license_tier: data.license_type || data.app_license_tier || this.appIdentity.app_license_tier || 'basic',
                        has_white_label: ['pro_plus', 'white_label'].includes(data.app_license_tier || this.appIdentity.app_license_tier),
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
                // Fetch branding from System settings
                const response = await api.get('/manage/system/settings/group/system');
                const data = response.data || {};
                
                this.appIdentity = {
                    ...this.appIdentity,
                    app_name: data.app_name || this.appIdentity.app_name || 'Jejakawan',
                    app_logo: data.app_logo || this.appIdentity.app_logo || '',
                    app_favicon: data.app_favicon || this.appIdentity.app_favicon || '',
                    app_license_tier: data.license_type || data.app_license_tier || this.appIdentity.app_license_tier || 'basic',
                    has_white_label: ['pro_plus', 'white_label'].includes(data.app_license_tier || this.appIdentity.app_license_tier),
                };

                applyFavicon(resolveFavicon([
                    this.appIdentity.app_favicon,
                    this.siteSettings.site_favicon,
                ]), { allowGeneric: this.publicSettingsLoaded });

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
                document.documentElement.classList.remove('no-transitions');
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
