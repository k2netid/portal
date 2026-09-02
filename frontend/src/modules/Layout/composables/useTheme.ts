import { logger } from '@/shared/utils/logger';
import { ref, computed, inject, type InjectionKey, type Ref, type ComputedRef } from 'vue';
import i18n from '@/engine/i18n';
import api from '@/engine/api/client';
import { JANARI_PRESETS, type JanariPresetKey } from '@/modules/Layout/config/janariPresets';
import { themeUsesJanariCanvas } from '@/modules/Layout/utils/themeManifest';
import { hexToHslString } from '@/shared/utils/color';
import { applyMergedSettingsSchema } from '@/modules/Layout/customizer/loaders/mergeThemeSettingsSchema';
import {
    isCustomizerPreviewQuery,
    isCustomizerThemeBootMessage,
    readStoredPreviewTheme,
    readThemeSlugFromQuery,
    storePreviewTheme,
} from '@/modules/Layout/customizer/preview/protocol';
import {
    FRONTEND_THEME_ACTIVATION_REV_KEY,
    FRONTEND_THEME_SNAPSHOT_KEY,
    clearFrontendThemeSnapshot,
    isThemeCardEmbedPreview,
    readFrontendThemeActivationRev,
    snapshotMatchesActivationRev,
} from '@/modules/Layout/utils/themeActivationSync';

export interface ThemeManifest {
    name?: string;
    version?: string;
    author?: string;
    /** Feature flags for runtime (e.g. janari_canvas for layout + accent pipeline). */
    supports?: Record<string, boolean | string>;
    settings_schema?: Record<string, ThemeSettingSchema>;
    [key: string]: unknown;
}

export interface ThemeSettingSchema {
    type:
        | 'text'
        | 'textarea'
        | 'color'
        | 'font'
        | 'typography'
        | 'select'
        | 'boolean'
        | 'checkbox'
        | 'checkbox_list'
        | 'range'
        | 'media';
    label?: string;
    default?: unknown;
    options?: unknown[];
}

export interface Theme {
    name: string;
    slug: string;
    type?: string;
    manifest?: ThemeManifest;
    settings?: Record<string, unknown>;
    assets?: {
        css?: string[];
        js?: string[];
    };
    custom_css?: string;
    /** DB column merged into API payload; mirrors manifest.supports for some installs */
    supports?: Record<string, boolean | string>;
    /** When present (API), used to detect theme row updates without deep-comparing manifest */
    updated_at?: string;
    parent_theme?: string | null;
    source?: 'bundled' | 'uploaded';
    bundle_url?: string | null;
    [key: string]: unknown;
}

/** When provided under the Visual Builder, theme reads stay canvas-scoped (no public activate / :root CSS). */
export type BuilderThemeOverride = {
    activeTheme: Ref<Theme | null> | ComputedRef<Theme | null>;
    themeSettings: Ref<Record<string, unknown>>;
};

export const BUILDER_THEME_OVERRIDE_KEY: InjectionKey<BuilderThemeOverride> = Symbol('builderThemeOverride');

const SYSTEM_FONTS = new Set([
    'sans-serif', 'serif', 'monospace', 'system-ui', '-apple-system',
    'blinkmacsystemfont', 'segoe ui', 'roboto', 'helvetica neue', 'arial',
    'courier new', 'georgia', 'times new roman', 'verdana', 'inherit', 'initial'
]);

const injectedFonts = new Set<string>();

export const injectGoogleFont = (fontFamily?: string): void => {
    if (typeof window === 'undefined' || !fontFamily) return;
    const cleanFont = fontFamily.replace(/['"]/g, '').trim();
    if (!cleanFont || SYSTEM_FONTS.has(cleanFont.toLowerCase()) || injectedFonts.has(cleanFont)) {
        return;
    }

    injectedFonts.add(cleanFont);

    // Preconnect links for Google Fonts if not present
    if (!document.querySelector('link[rel="preconnect"][href="https://fonts.googleapis.com"]')) {
        const preconnect1 = document.createElement('link');
        preconnect1.rel = 'preconnect';
        preconnect1.href = 'https://fonts.googleapis.com';
        document.head.appendChild(preconnect1);

        const preconnect2 = document.createElement('link');
        preconnect2.rel = 'preconnect';
        preconnect2.href = 'https://fonts.gstatic.com';
        preconnect2.crossOrigin = 'anonymous';
        document.head.appendChild(preconnect2);
    }

    const fontQuery = encodeURIComponent(cleanFont).replace(/%20/g, '+');
    const linkId = `google-font-${cleanFont.toLowerCase().replace(/[^a-z0-9]/g, '-')}`;
    if (!document.getElementById(linkId)) {
        const link = document.createElement('link');
        link.id = linkId;
        link.rel = 'stylesheet';
        link.href = `https://fonts.googleapis.com/css2?family=${fontQuery}:wght@300;400;500;600;700;800;900&display=swap`;
        document.head.appendChild(link);
    }
};

// Global shared state
const activeTheme = ref<Theme | null>(null);
const themeSettings = ref<Record<string, unknown>>({});
const themeAssets = ref<{ css: string[]; js: string[] }>({ css: [], js: [] });
const customCss = ref('');
const cssVariables = ref('');
const loading = ref(false);
const error = ref<string | null>(null);
const isLoading = ref(false); // Prevent multiple simultaneous loads
let activeLoadPromise: Promise<void> | null = null; // Shared promise to await if load is in progress
let themeUpdateListener: ((event: MessageEvent) => void) | null = null;
let lastLoadedAt = 0;
const THEME_CACHE_TTL_MS = 60_000;
const THEME_SNAPSHOT_KEY = FRONTEND_THEME_SNAPSHOT_KEY;
let themeActivationListenerInstalled = false;
let sharedForceReloadActiveTheme: ((type?: string) => Promise<void>) | null = null;
const readThemeSnapshot = (): {
    activeTheme: Theme | null;
    themeSettings: Record<string, unknown>;
    themeAssets: { css: string[]; js: string[] };
    customCss: string;
    lastLoadedAt: number;
} | null => {
    if (typeof window === 'undefined') return null;
    try {
        const raw = window.sessionStorage.getItem(THEME_SNAPSHOT_KEY);
        if (!raw) return null;
        const parsed = JSON.parse(raw) as {
            activeTheme?: Theme | null;
            themeSettings?: Record<string, unknown>;
            themeAssets?: { css?: string[]; js?: string[] };
            customCss?: string;
            lastLoadedAt?: number;
        };
        return {
            activeTheme: parsed.activeTheme ?? null,
            themeSettings: parsed.themeSettings ?? {},
            themeAssets: {
                css: Array.isArray(parsed.themeAssets?.css) ? parsed.themeAssets!.css : [],
                js: Array.isArray(parsed.themeAssets?.js) ? parsed.themeAssets!.js : [],
            },
            customCss: typeof parsed.customCss === 'string' ? parsed.customCss : '',
            lastLoadedAt: Number.isFinite(parsed.lastLoadedAt) ? Number(parsed.lastLoadedAt) : 0,
        };
    } catch {
        return null;
    }
};

const writeThemeSnapshot = (): void => {
    if (typeof window === 'undefined') return;
    try {
        window.sessionStorage.setItem(THEME_SNAPSHOT_KEY, JSON.stringify({
            activeTheme: activeTheme.value,
            themeSettings: themeSettings.value,
            themeAssets: themeAssets.value,
            customCss: customCss.value,
            lastLoadedAt,
        }));
    } catch {
        // ignore storage quota / privacy mode errors
    }
};

const initialThemeSnapshot = readThemeSnapshot();
const initialEmbedPreview = typeof window !== 'undefined' && isThemeCardEmbedPreview();
const initialActivationRev = readFrontendThemeActivationRev();
if (typeof window !== 'undefined') {
    const search = window.location.search;
    const previewSlug = isCustomizerPreviewQuery(search) ? readThemeSlugFromQuery(search) : null;
    const snapSlug =
        typeof initialThemeSnapshot?.activeTheme?.slug === 'string'
            ? initialThemeSnapshot.activeTheme.slug.toLowerCase()
            : '';

    if (initialEmbedPreview) {
        clearFrontendThemeSnapshot();
        lastLoadedAt = 0;
    } else if (
        initialThemeSnapshot
        && initialActivationRev?.slug
        && !snapshotMatchesActivationRev(initialThemeSnapshot.activeTheme?.slug)
    ) {
        clearFrontendThemeSnapshot();
        lastLoadedAt = 0;
    } else if (previewSlug && snapSlug && snapSlug !== previewSlug) {
        // Customizer iframe: never first-paint the wrong package (kills click-highlight targets).
        activeTheme.value = {
            name: previewSlug,
            slug: previewSlug,
            type: 'frontend',
        };
        lastLoadedAt = 0;
    } else if (initialThemeSnapshot && snapshotMatchesActivationRev(initialThemeSnapshot.activeTheme?.slug)) {
        activeTheme.value = initialThemeSnapshot.activeTheme;
        themeSettings.value = initialThemeSnapshot.themeSettings;
        themeAssets.value = initialThemeSnapshot.themeAssets;
        customCss.value = initialThemeSnapshot.customCss;
        lastLoadedAt = 0;
    } else if (previewSlug) {
        activeTheme.value = {
            name: previewSlug,
            slug: previewSlug,
            type: 'frontend',
        };
    }
} else if (initialThemeSnapshot) {
    activeTheme.value = initialThemeSnapshot.activeTheme;
    themeSettings.value = initialThemeSnapshot.themeSettings;
    themeAssets.value = initialThemeSnapshot.themeAssets;
    customCss.value = initialThemeSnapshot.customCss;
    lastLoadedAt = 0;
}

/**
 * Composable for theme management in frontend
 */
export function useTheme() {
    /** Builder canvas: read builder themeData/settings; never mutate public :root / activate. */
    const builderOverride = inject(BUILDER_THEME_OVERRIDE_KEY, null);

    /**
     * Load active theme
     */
    const loadActiveTheme = async (type = 'frontend', options?: { force?: boolean }) => {
        if (builderOverride) {
            return;
        }
        const force = options?.force === true;
        const embedPreview = typeof window !== 'undefined' && isThemeCardEmbedPreview();

        // Drop stale public snapshot before a forced reconcile (not in customizer iframe).
        if ((force || embedPreview) && type === 'frontend' && typeof window !== 'undefined') {
            const search = window.location.search;
            if (!isCustomizerPreviewQuery(search)) {
                clearFrontendThemeSnapshot();
                lastLoadedAt = 0;
            }
        }

        // Return existing promise if already loading (force shares the in-flight fetch).
        if (activeLoadPromise && type === 'frontend') {
            return activeLoadPromise;
        }

        const cacheIsFresh = Date.now() - lastLoadedAt < THEME_CACHE_TTL_MS;
        // Return immediately if cached theme is still fresh
        if (activeTheme.value && type === 'frontend' && !loading.value && !force && !embedPreview && cacheIsFresh) {
            return;
        }

        isLoading.value = true;
        loading.value = true;
        error.value = null;

        activeLoadPromise = (async () => {
            let previewMode = false;
            try {
            let data: Theme | null = null;

            // Customizer iframe only: prefer the theme being edited, not the public active theme.
            // Gate on real preview context — never let leftover session keys override public `/`.
            // sessionStorage is same-origin shared with the console tab; iframe-only flag avoids that.
            if (type === 'frontend' && typeof window !== 'undefined') {
                const search = window.location.search;
                const inIframe = (() => {
                    try {
                        return window.parent !== window;
                    } catch {
                        return true;
                    }
                })();
                const previewFromQuery = isCustomizerPreviewQuery(search);
                previewMode = previewFromQuery
                    || (inIframe
                        && typeof sessionStorage !== 'undefined'
                        && sessionStorage.getItem('ja_customizer_preview') === '1');

                if (previewMode) {
                    const previewSlug = readThemeSlugFromQuery(search)
                        || (typeof sessionStorage !== 'undefined'
                            ? String(sessionStorage.getItem('ja_customizer_theme_slug') || '').toLowerCase() || null
                            : null);

                    if (previewSlug) {
                        try {
                            sessionStorage.setItem('ja_customizer_preview', '1');
                            sessionStorage.setItem('ja_customizer_theme_slug', previewSlug);
                        } catch { /* ignore */ }

                        const stored = readStoredPreviewTheme(previewSlug);
                        if (stored && typeof stored.slug === 'string') {
                            data = stored as Theme;
                        }

                        if (!data) {
                            try {
                                const manageRes = await api.get(`/manage/layout/themes/${previewSlug}`);
                                let manageData = manageRes.data;
                                if (manageData && typeof manageData === 'object' && 'success' in manageData && 'data' in manageData) {
                                    manageData = manageData.data;
                                }
                                if (manageData && typeof manageData.slug === 'string') {
                                    data = manageData as Theme;
                                }
                            } catch {
                                /* unauthenticated public shell — wait for THEME_BOOT from parent */
                            }
                        }
                    } else {
                        const stored = readStoredPreviewTheme();
                        if (stored && typeof stored.slug === 'string') {
                            data = stored as Theme;
                        }
                    }
                }
            }

            if (!data) {
            // Use public endpoint for frontend theme (no auth required)
            const endpoint = type === 'frontend'
                ? `/public/layout/themes/active?type=${type}`
                : `/manage/layout/themes/active?type=${type}`;

            const response = await api.get(endpoint);
            data = response.data;

            // Standardized API response unwrapping (defensive)
            if (data && typeof data === 'object' && 'success' in data && 'data' in data) {
                const wrapped = data as { data?: Theme | null };
                data = wrapped.data ?? null;
            }
            }

            // Handle null response (no active theme) — Janari is the CMS default reference theme
            if (!data) {
                 activeTheme.value = {
                     name: 'Janari',
                     slug: 'janari',
                     type: 'frontend',
                 };
                 return;
            }
            if (typeof (data as Theme).slug !== 'string' || (data as Theme).slug.trim() === '') {
                (data as Theme).slug = 'janari';
                if (!(data as Theme).name) {
                    (data as Theme).name = 'Janari';
                }
                if (!(data as Theme).type) {
                    (data as Theme).type = 'frontend';
                }
            }
            // Compare fields that affect UI; avoid stringifying entire payload (manifest is large).
            const prev = activeTheme.value
            const nextSlug = (data as Theme)?.slug
            const nextSettings = JSON.stringify((data as Theme)?.settings ?? {})
            const prevSettings = JSON.stringify(prev?.settings ?? {})
            const nextCss = String((data as Theme)?.custom_css ?? '')
            const prevCss = String(prev?.custom_css ?? '')
            const nextAssetsSig = JSON.stringify({
                css: (data as Theme)?.assets?.css ?? [],
                js: (data as Theme)?.assets?.js ?? [],
            })
            const prevAssetsSig = JSON.stringify({
                css: (prev as Theme | null)?.assets?.css ?? [],
                js: (prev as Theme | null)?.assets?.js ?? [],
            })
            const nextUpdated = typeof (data as Theme)?.updated_at === 'string' ? (data as Theme).updated_at : ''
            const prevUpdated = typeof prev?.updated_at === 'string' ? prev.updated_at : ''

            if (!prev || prev.slug !== nextSlug || nextSettings !== prevSettings || nextCss !== prevCss
                || nextAssetsSig !== prevAssetsSig || nextUpdated !== prevUpdated) {
                applyMergedSettingsSchema(data as Theme, (data as Theme).slug);
                activeTheme.value = data;
                themeSettings.value = data.settings || {};
            }

            // Load theme assets
            if (nextAssetsSig !== prevAssetsSig && data.assets) {
                themeAssets.value = {
                    css: Array.isArray(data.assets.css) ? data.assets.css : [],
                    js: Array.isArray(data.assets.js) ? data.assets.js : [],
                };
                injectCssFiles(themeAssets.value.css);
                injectJsFiles(themeAssets.value.js);
            }

            if (nextCss !== prevCss && data.custom_css) {
                customCss.value = data.custom_css;
                applyCustomCss();
            }

            applyThemeStyles();

            // Sync Janari canvas accent variables when theme declares support (or legacy janari* slug)
            if (themeUsesJanariCanvas(data as Theme)) {
                syncJanariStyles(themeSettings.value);
            }

            // Add listener for Theme Customizer updates if in frontend
            if (type === 'frontend' && typeof window !== 'undefined') {
                if (themeUpdateListener) {
                    window.removeEventListener('message', themeUpdateListener);
                }
                themeUpdateListener = (event: MessageEvent) => {
                    const parentOrigin = (() => {
                        try {
                            const raw = new URLSearchParams(window.location.search).get('ja_parent_origin');
                            return raw ? decodeURIComponent(raw) : null;
                        } catch {
                            return null;
                        }
                    })();
                    const isAllowed = event.origin === window.location.origin || (parentOrigin && event.origin === parentOrigin);
                    if (!isAllowed) {
                        return;
                    }
                    if (isCustomizerThemeBootMessage(event.data)) {
                        const boot = event.data.theme as Theme;
                        if (typeof boot.slug === 'string' && boot.slug.trim()) {
                            applyMergedSettingsSchema(boot, boot.slug);
                            activeTheme.value = boot;
                            themeSettings.value = (boot.settings || {}) as Record<string, unknown>;
                            customCss.value = String(boot.custom_css || '');
                            storePreviewTheme(boot as unknown as Record<string, unknown>);
                            try {
                                sessionStorage.setItem('ja_customizer_preview', '1');
                                sessionStorage.setItem('ja_customizer_theme_slug', boot.slug);
                            } catch { /* ignore */ }
                            applyCustomCss();
                            applyThemeStyles();
                            if (themeUsesJanariCanvas(boot)) {
                                syncJanariStyles(themeSettings.value);
                            }
                        }
                        return;
                    }
                    if (event.data && (event.data.type === 'THEME_UPDATE' || event.data.type === 'JA_THEME_CUSTOMIZER_SYNC')) {
                        const incomingSettings = (event.data.settings || event.data.theme?.settings) as Record<string, unknown> | undefined;
                        if (incomingSettings) {
                            themeSettings.value = {
                                ...themeSettings.value,
                                ...incomingSettings
                            };
                            if (activeTheme.value) {
                                activeTheme.value = {
                                    ...activeTheme.value,
                                    settings: {
                                        ...(activeTheme.value.settings || {}),
                                        ...incomingSettings
                                    }
                                };
                            }

                            applyThemeStyles();

                            if (themeUsesJanariCanvas(activeTheme.value)) {
                                requestAnimationFrame(() => {
                                    syncJanariStyles(themeSettings.value);
                                });
                            }

                            window.dispatchEvent(new CustomEvent('theme-animation-settings-changed', {
                                detail: { settings: themeSettings.value }
                            }));
                        }

                        const incomingCss = event.data.custom_css ?? event.data.theme?.custom_css;
                        if (incomingCss !== undefined) {
                            customCss.value = String(incomingCss);
                            applyCustomCss();
                        }
                    }
                };
                window.addEventListener('message', themeUpdateListener);
            }

            lastLoadedAt = Date.now();
            error.value = null;
            // Preview drafts + theme-card embed must not poison public sessionStorage.
            if (!previewMode && !embedPreview) {
                writeThemeSnapshot();
            }

        } catch (err: unknown) {
            const errorObj = err as Error;
            logger.warning('Failed to load active theme:', err);
            error.value = errorObj.message || i18n.global.t('layout.themes.loadFailed');
            // Keep last known good theme to prevent full layout fallback flicker
            // during transient network/backend failures.
            } finally {
                loading.value = false;
                isLoading.value = false;
                activeLoadPromise = null;
            }
        })();

        return activeLoadPromise;
    };

    /**
     * Get theme setting with fallback
     */
    const getSetting = (key: string, defaultValue: unknown = null) => {
        if (!activeTheme.value) {
            return defaultValue;
        }

        if (themeSettings.value && themeSettings.value[key] !== undefined) {
            return themeSettings.value[key];
        }

        const manifest = activeTheme.value.manifest;
        if (manifest && manifest.settings_schema && manifest.settings_schema[key]) {
            return manifest.settings_schema[key].default ?? defaultValue;
        }

        return defaultValue;
    };

    /**
     * Apply theme styles (CSS variables)
     */
    const applyThemeStyles = () => {
        if (!activeTheme.value) return;

        const variables: string[] = [];
        const manifest = activeTheme.value.manifest;

        if (manifest && manifest.settings_schema) {
            Object.keys(manifest.settings_schema).forEach(key => {
                const setting = manifest.settings_schema![key];
                if (!setting) return;
                const value = getSetting(key, setting.default);

                if (value === undefined || value === null) return;

                const cssKey = '--theme-' + key.replace(/_/g, '-');

                if (setting.type === 'color') {
                    const colorValue = value as string;
                    variables.push(`${cssKey}: ${colorValue};`);

                    // Inject HSL version for Shadcn compatibility
                    const hslValue = hexToHslString(colorValue);
                    if (hslValue) {
                        variables.push(`${cssKey}-hsl: ${hslValue};`);
                    }
                } else if (setting.type === 'font' || setting.type === 'typography') {
                    // Inject dynamic Google Font and CSS custom property
                    const rawFont = String(value);
                    injectGoogleFont(rawFont);
                    const fontValue = rawFont.includes(' ') ? `"${rawFont}"` : rawFont;
                    variables.push(`${cssKey}: ${fontValue};`);
                }
            });
        }

        if (variables.length > 0) {
            const newCss = `:root {\n  ${variables.join('\n  ')}\n}`;
            
            // STABILITY GUARD: Only update ref and DOM if content changed
            if (cssVariables.value !== newCss) {
                cssVariables.value = newCss;
                injectCssString(cssVariables.value, 'theme-variables');
            }
        }
    };

    /**
     * Consolidated Janari style sync.
     *
     * NOTE:
     * Layout-level Janari data attributes in `FrontendLayout` are the single source of truth
     * for background/surface variants. This function intentionally syncs accent variables only
     * to avoid races between multiple CSS injectors updating the same tokens.
     */
    const syncJanariStyles = (settings: Record<string, unknown>) => {
        // Resolve accent variables
        const preset = String(settings.color_preset || 'custom');
        let lAcc: string;
        let dAcc: string;

        if (preset !== 'custom' && JANARI_PRESETS[preset as JanariPresetKey]) {
            const p = JANARI_PRESETS[preset as JanariPresetKey];
            lAcc = p.hslLight;
            dAcc = p.hslDark;
        } else {
            const hex = String(settings.color_primary || '#000000');
            const hsl = hexToHslString(hex) || '0 0% 0%';
            lAcc = hsl;
            dAcc = hsl.replace(/\d+%/g, (m, i) => i === 2 ? '100%' : m); // Boost brightness for dark mode custom
        }

        // Keep this injector narrowly scoped to accent-only variables.
        const css = `
            .theme-janari {
                --janari-accent-hsl-inline: ${lAcc};
                --janari-accent-hsl-inline-dark: ${dAcc};
            }
            .dark .theme-janari {
                --janari-accent-hsl-inline: ${lAcc};
                --janari-accent-hsl-inline-dark: ${dAcc};
            }
        `;

        injectCssString(css, 'janari-dynamic-theme');
    };
    /**
     * Apply custom CSS
     */
    const applyCustomCss = () => {
        if (customCss.value) {
            injectCssString(customCss.value, 'theme-custom-css');
        }
    };

    /**
     * Inject CSS files into document
     */
    const injectCssFiles = (cssFiles: string[]) => {
        if (!document.head) return;
        cssFiles.forEach((cssFile, index) => {
            const href = cssFile.startsWith('http') || cssFile.startsWith('/') ? cssFile : `/${cssFile}`;
            const linkId = `theme-css-${index}`;
            const existing = document.getElementById(linkId) as HTMLLinkElement | null;
            if (existing && existing.getAttribute('href') === href) {
                return;
            }
            if (existing) existing.remove();

            const link = document.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        });
    };

    /**
     * Inject JS files into document
     */
    const injectJsFiles = (jsFiles: string[]) => {
        if (!document.head) return;
        jsFiles.forEach((jsFile, index) => {
            const src = jsFile.startsWith('http') || jsFile.startsWith('/') ? jsFile : `/${jsFile}`;
            const scriptId = `theme-js-${index}`;
            const existing = document.getElementById(scriptId) as HTMLScriptElement | null;
            if (existing && existing.getAttribute('src') === src) {
                return;
            }
            if (existing) existing.remove();

            const script = document.createElement('script');
            script.id = scriptId;
            script.src = src;
            script.defer = true;
            document.head.appendChild(script);
        });
    };

    /**
     * Inject CSS string into document
     */
    const injectCssString = (css: string, id: string) => {
        if (!document.head || !css) return;
        
        const existing = document.getElementById(id) as HTMLStyleElement;
        
        // STABILITY GUARD: Only update if content is actually different
        // This prevents infinite reactivity loops when this is called from computed/watchers
        if (existing && existing.textContent === css) {
            return;
        }

        if (existing) {
            existing.textContent = css;
        } else {
            const style = document.createElement('style');
            style.id = id;
            style.textContent = css;
            document.head.appendChild(style);
        }
    };

    const isThemeLoaded = computed(() => activeTheme.value !== null);
    const themeName = computed(() => activeTheme.value?.name || i18n.global.t('layout.themes.defaultName'));
    const themeType = computed(() => activeTheme.value?.type || 'frontend');

    if (builderOverride) {
        const getOverrideSetting = (key: string, defaultValue: unknown = null) => {
            if (builderOverride.themeSettings.value && builderOverride.themeSettings.value[key] !== undefined) {
                return builderOverride.themeSettings.value[key];
            }
            const manifest = builderOverride.activeTheme.value?.manifest as ThemeManifest | undefined;
            if (manifest?.settings_schema?.[key]) {
                return manifest.settings_schema[key].default ?? defaultValue;
            }
            return defaultValue;
        };

        return {
            activeTheme: builderOverride.activeTheme as Ref<Theme | null>,
            themeSettings: builderOverride.themeSettings,
            themeAssets,
            customCss,
            cssVariables,
            loading,
            error,
            isThemeLoaded: computed(() => builderOverride.activeTheme.value !== null),
            themeName: computed(() => builderOverride.activeTheme.value?.name || i18n.global.t('layout.themes.defaultName')),
            themeType: computed(() => builderOverride.activeTheme.value?.type || 'frontend'),
            loadActiveTheme,
            getSetting: getOverrideSetting,
            // Canvas.injectThemeStyles owns scoped CSS — never write #theme-variables on document.
            applyThemeStyles: () => {},
        };
    }

    // Public shell only — never bind reload to a builder-scoped no-op closure.
    sharedForceReloadActiveTheme = (type = 'frontend') => loadActiveTheme(type, { force: true });
    if (typeof window !== 'undefined' && !themeActivationListenerInstalled) {
        themeActivationListenerInstalled = true;
        const reloadFromActivation = () => {
            void sharedForceReloadActiveTheme?.('frontend');
        };
        window.addEventListener('storage', (event: StorageEvent) => {
            if (event.key === FRONTEND_THEME_ACTIVATION_REV_KEY) {
                reloadFromActivation();
            }
        });
        window.addEventListener('ja-frontend-theme-activated', reloadFromActivation);
    }

    return {
        activeTheme,
        themeSettings,
        themeAssets,
        customCss,
        cssVariables,
        loading,
        error,
        isThemeLoaded,
        themeName,
        themeType,
        loadActiveTheme,
        getSetting,
        applyThemeStyles,
    };
}
