import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { normalizeLocaleCode } from '@/engine/i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';

/**
 * Resolves theme customizer values per active locale.
 * Order: settings[`${key}_${locale}`] → settings[key] → manifest default → fallback.
 */
export function useLocalizedThemeSetting() {
    const { locale } = useI18n({ useScope: 'global' });
    const { getSetting } = useTheme();

    const languageCode = computed(() => normalizeLocaleCode(locale.value));

    const localizedSetting = (key: string, defaultValue: unknown = null): unknown => {
        const lang = languageCode.value;
        const localizedKey = `${key}_${lang}`;
        const localized = getSetting(localizedKey);
        if (localized !== null && localized !== undefined && String(localized).trim() !== '') {
            return localized;
        }
        const base = getSetting(key);
        if (base !== null && base !== undefined && String(base).trim() !== '') {
            return base;
        }
        return defaultValue;
    };

    const localizedString = (key: string, defaultValue = ''): string => {
        const value = localizedSetting(key, defaultValue);
        return value != null ? String(value) : defaultValue;
    };

    return {
        locale,
        languageCode,
        localizedSetting,
        localizedString,
    };
}
