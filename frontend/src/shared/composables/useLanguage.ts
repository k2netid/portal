import { logger } from '@/shared/utils/logger';
import { ref, computed } from 'vue';
import i18n, { normalizeLocaleCode, setLocale as i18nSetLocale, getLocale as i18nGetLocale, loadLocaleMessages } from '@/engine/i18n';
import api from '@/engine/api/client';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';

export interface Language {
    id?: number;
    code: string;
    name: string;
    native_name?: string;
    flag?: string;
    is_default?: boolean;
    is_active?: boolean;
}

const currentLanguage = ref<Language | null>(null);
const languages = ref<Language[]>([]);
const loading = ref(false);
const initialized = ref(false);

const hasPersistedUser = (): boolean => {
    const userRaw = localStorage.getItem('user');
    if (!userRaw) return false;

    try {
        const parsed = JSON.parse(userRaw);
        return !!parsed && typeof parsed === 'object';
    } catch {
        return false;
    }
};

// RTL language codes
const RTL_LANGUAGES = ['ar', 'he', 'fa', 'ur', 'yi'];

/**
 * Composable for language management
 * 
 * IMPORTANT: This composable syncs with Vue I18n (i18n.js).
 * Priority: Backend (if authenticated) → localStorage → Browser detect → Default
 */
export function useLanguage() {
    /**
     * Check if language is RTL
     */
    const isRTL = computed(() => {
        if (!currentLanguage.value) return false;
        return RTL_LANGUAGES.includes(currentLanguage.value.code.toLowerCase());
    });

    /**
     * Get current language code
     */
    const currentLanguageCode = computed(() => {
        return currentLanguage.value?.code || i18nGetLocale();
    });

    /**
     * Load available languages from API
     */
    const loadLanguages = async () => {
        loading.value = true;
        try {
            const response = await api.get('/public/system/languages');
            const { data } = parseResponse(response);
            languages.value = ensureArray(data);
        } catch (error) {
            logger.error('Failed to load languages:', error);
            languages.value = [];
        } finally {
            loading.value = false;
        }
    };

    // Check if user is authenticated
    const isAuthenticated = () => {
        return hasPersistedUser();
    };

    /**
     * Load locale preference from backend (if authenticated)
     */
    const loadFromBackend = async (): Promise<string | null> => {
        if (!isAuthenticated()) return null;

        try {
            const response = await api.get('/manage/system/profile/preferences');
            if (response.data?.locale) {
                return response.data.locale;
            }
        } catch (error: unknown) {
            logger.warning('Failed to load locale from backend:', (error as Error).message);
        }
        return null;
    };

    /**
     * Sync locale with backend
     */
    const syncLanguageWithBackend = async (locale: string) => {
        if (!isAuthenticated()) return;

        try {
            await api.put('/manage/system/profile/preferences', { locale });
        } catch (error: unknown) {
            logger.warning('Locale sync failed:', (error as Error).message);
        }
    };

    /**
     * Set current language with full persistence
     */
    const setLanguage = async (languageCode: string) => {
        const resolved = normalizeLocaleCode(languageCode);
        // Ensure locale messages are loaded
        await loadLocaleMessages(resolved);
        // Update Vue I18n (this saves to localStorage)
        i18nSetLocale(resolved);

        // Find language object for UI
        const language = languages.value.find(l => normalizeLocaleCode(l.code) === resolved)
            ?? languages.value.find(l => l.code === languageCode);
        if (language) {
            currentLanguage.value = language;
        } else {
            // If language not in list, create a minimal object
            currentLanguage.value = { code: resolved, name: resolved.toUpperCase() };
        }

        // Update document attributes
        updateDocumentLanguage(resolved);

        // Sync with backend
        syncLanguageWithBackend(resolved);
    };

    /**
     * Update document language and RTL
     */
    const updateDocumentLanguage = (languageCode: string) => {
        if (typeof document === 'undefined') return;

        // Set lang attribute
        document.documentElement.setAttribute('lang', languageCode);

        // Handle RTL
        const isRTLLang = RTL_LANGUAGES.includes(languageCode.toLowerCase());
        if (isRTLLang) {
            document.documentElement.setAttribute('dir', 'rtl');
            document.documentElement.classList.add('rtl');
        } else {
            document.documentElement.setAttribute('dir', 'ltr');
            document.documentElement.classList.remove('rtl');
        }
    };

    /**
     * Initialize language from backend/localStorage
     * 
     * Priority:
     * 1. Backend (if authenticated - source of truth)
     * 2. localStorage['locale'] (fallback)
     * 3. Browser language (if supported)
     * 4. Database default
     */
    const initializeLanguage = async () => {
        // Prevent double initialization
        if (initialized.value) {
            return;
        }
        initialized.value = true;

        // Load languages for dropdown
        await loadLanguages();

        // Try to load from backend first (source of truth for logged-in users)
        const backendLocale = await loadFromBackend();

        let currentLocale;
        if (backendLocale) {
            // Backend is source of truth - also update localStorage and Vue I18n
            currentLocale = backendLocale;
            await loadLocaleMessages(backendLocale);
            i18nSetLocale(backendLocale);
        } else {
            // Fallback to Vue I18n (which already read from localStorage/browser)
            currentLocale = i18nGetLocale();
        }

        // Find matching language object
        let language = languages.value.find(l => l.code === currentLocale);

        if (!language && languages.value.length > 0) {
            // Locale not found in DB, but we still honor user preference
            language = { code: currentLocale, name: currentLocale.toUpperCase() };
        }

        if (language) {
            currentLanguage.value = language;
            updateDocumentLanguage(language.code);
        }
    };

    /**
     * Reset initialization flag (for testing)
     */
    const resetInitialization = () => {
        initialized.value = false;
    };

    /**
     * Get flag emoji or icon for language
     */
    const getLanguageFlag = (language: string | Language | null) => {
        if (!language) return '🌐';

        // Handle string (language code)
        if (typeof language === 'string') {
            const flagMap: Record<string, string> = {
                'en': '🇺🇸', 'id': '🇮🇩', 'ar': '🇸🇦', 'he': '🇮🇱',
                'fr': '🇫🇷', 'de': '🇩🇪', 'es': '🇪🇸', 'pt': '🇵🇹',
                'zh': '🇨🇳', 'ja': '🇯🇵', 'ko': '🇰🇷', 'ru': '🇷🇺',
            };
            const code = language.toLowerCase().split('-')[0] || '';
            return (flagMap as Record<string, string>)[code] || '🌐';
        }

        // Handle language object with flag property
        if (language.flag) return language.flag;

        // Fallback to emoji flags
        const flagMap: Record<string, string> = {
            'en': '🇺🇸', 'id': '🇮🇩', 'ar': '🇸🇦', 'he': '🇮🇱',
            'fr': '🇫🇷', 'de': '🇩🇪', 'es': '🇪🇸', 'pt': '🇵🇹',
            'zh': '🇨🇳', 'ja': '🇯🇵', 'ko': '🇰🇷', 'ru': '🇷🇺',
        };

        if (!language.code) return '🌐';
        const code = (language.code || '').toLowerCase().split('-')[0] || '';
        return (flagMap as Record<string, string>)[code] || '🌐';
    };

    /**
     * Translate text using Vue I18n
     */
    const t = (key: string, params: Record<string, unknown> = {}) => {
        const i18nGlobal = i18n.global as unknown as { t: (key: string, params: Record<string, unknown>) => string };
        if (i18nGlobal) {
            return i18nGlobal.t(key, params);
        }
        return key;
    };

    return {
        currentLanguage,
        languages,
        loading,
        isRTL,
        currentLanguageCode,
        loadLanguages,
        setLanguage,
        getLanguageFlag,
        t,
        initializeLanguage,
        resetInitialization,
        loadFromBackend,
    };
}
