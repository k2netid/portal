import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';

/**
 * Theme-scoped i18n for in-tree theme Vue files.
 *
 * Pass the **package folder slug** of the calling component (e.g. useThemeI18n('janari')).
 * Keys are tried in order:
 *   1. theme.<activeSlug>.*     (child / active overrides)
 *   2. theme.<parent_theme>.*   (parent chain)
 *   3. theme.<packageSlug>.*    (locale JSON that ships with this Vue file)
 *
 * This avoids missing-key noise when ThemePageResolver falls back across bundled
 * themes (e.g. Sarangenge active → Janari Tim.vue still needs theme.janari.pages.team).
 */
export function useThemeI18n(packageSlug = 'janari') {
    const { t, te, locale, ...rest } = useI18n();
    const { activeTheme } = useTheme();

    const candidateSlugs = computed(() => {
        const out: string[] = [];
        const seen = new Set<string>();
        const push = (raw: unknown) => {
            if (typeof raw !== 'string') return;
            const s = raw.trim();
            if (!s) return;
            const key = s.toLowerCase();
            if (seen.has(key)) return;
            seen.add(key);
            out.push(s);
        };

        push(activeTheme.value?.slug);
        push(activeTheme.value?.parent_theme);
        push(packageSlug);

        return out;
    });

    const prefix = computed(() => `theme.${candidateSlugs.value[0] || packageSlug}`);

    const themeKey = (key: string, slug?: string) =>
        `theme.${slug || candidateSlugs.value[0] || packageSlug}.${key}`;

    const tt = ((key: string, ...args: unknown[]) => {
        for (const slug of candidateSlugs.value) {
            const full = themeKey(key, slug);
            if (te(full)) {
                // @ts-expect-error vue-i18n overload
                return t(full, ...args);
            }
        }
        // Last resort: package slug (shows missing-key path under the correct theme)
        // @ts-expect-error vue-i18n overload
        return t(themeKey(key, packageSlug), ...args);
    }) as typeof t;

    const tte = (key: string) => candidateSlugs.value.some((slug) => te(themeKey(key, slug)));

    return {
        ...rest,
        locale,
        slug: computed(() => candidateSlugs.value[0] || packageSlug),
        prefix,
        t: tt,
        te: tte,
        globalKey: (key: string) => themeKey(key, packageSlug),
    };
}
