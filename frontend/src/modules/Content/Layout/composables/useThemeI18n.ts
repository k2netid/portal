import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Content/Layout/composables/useTheme';

/**
 * Theme-scoped i18n: resolves keys under theme.<activeSlug>.*.
 * Use relative keys in theme Vue files, e.g. t('pages.contact.title').
 */
export function useThemeI18n(fallbackSlug = 'janari') {
    const { t, te, locale, ...rest } = useI18n();
    const { activeTheme } = useTheme();

    const slug = computed(() => {
        const s = activeTheme.value?.slug;
        return typeof s === 'string' && s.length > 0 ? s : fallbackSlug;
    });

    const prefix = computed(() => `theme.${slug.value}`);

    const themeKey = (key: string) => `${prefix.value}.${key}`;

    const tt = ((key: string, ...args: unknown[]) => {
        // @ts-expect-error vue-i18n overload
        return t(themeKey(key), ...args);
    }) as typeof t;

    const tte = (key: string) => te(themeKey(key));

    return {
        ...rest,
        locale,
        slug,
        prefix,
        t: tt,
        te: tte,
        globalKey: themeKey,
    };
}
