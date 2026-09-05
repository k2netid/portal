import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';

function trimStr(value: unknown): string {
    if (value == null) {
        return '';
    }
    if (typeof value !== 'string') {
        return String(value).trim();
    }
    return value.trim();
}

/** Site name + logo for member chrome (always show name beside logo in portal). */
export function useMemberPortalBranding() {
    const { t } = useI18n({ useScope: 'global' });
    const { getSetting } = useTheme();
    const systemStore = useSystemStore();

    const siteName = computed(() => {
        const fromTheme = trimStr(getSetting('site_title', ''))
            || trimStr(getSetting('site_name', ''))
            || trimStr(getSetting('brand_name', ''));
        if (fromTheme) {
            return fromTheme;
        }
        const fromSettings = trimStr(systemStore.settings?.site_name);
        if (fromSettings) {
            return fromSettings;
        }
        return t('common.labels.app.name', 'Jejakawan');
    });

    const siteTagline = computed(() => {
        const fromTheme = trimStr(getSetting('site_description', ''));
        if (fromTheme) {
            return fromTheme;
        }
        return trimStr(systemStore.settings?.site_description);
    });

    const siteLogo = computed((): string => {
        const fromSetting = getSetting('brand_logo');
        const fromSite = (systemStore.siteSettings?.site_logo || systemStore.settings?.site_logo);
        return (typeof fromSetting === 'string' ? fromSetting : '')
            || (typeof fromSite === 'string' ? fromSite : '');
    });

    return {
        siteName,
        siteTagline,
        siteLogo,
    };
}
