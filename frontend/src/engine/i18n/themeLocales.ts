/**
 * Bundled public theme locale packages (co-located under views/themes/<slug>/locales).
 * Keys resolve as theme.<slug>.* (e.g. theme.janari.pages.contact.title).
 *
 * When themes ship as extensions, register additional slugs via registerThemeLocales().
 */
import type { I18n } from 'vue-i18n';
import janari from '@/modules/Content/Layout/views/themes/janari/locales';

const bundledThemeMessages = {
    en: { janari: janari.en },
    id: { janari: janari.id },
    su: { janari: janari.su },
} as const;

export type ThemeLocaleSlug = keyof typeof bundledThemeMessages.en;

export const themeLocaleBundles = bundledThemeMessages;

/** Merge locale messages for a theme slug (extension install / hot-load). */
export function registerThemeLocales(
    i18n: I18n,
    slug: string,
    messages: { en: Record<string, unknown>; id: Record<string, unknown>; su?: Record<string, unknown> },
): void {
    for (const lang of ['en', 'id', 'su'] as const) {
        if (lang === 'su' && !messages.su) continue;
        const existing = (i18n.global.getLocaleMessage(lang) as Record<string, unknown>).theme;
        const themeRoot =
            existing && typeof existing === 'object' && !Array.isArray(existing)
                ? { ...(existing as Record<string, unknown>) }
                : {};
        themeRoot[slug] = messages[lang];
        i18n.global.mergeLocaleMessage(lang, { theme: themeRoot });
    }
}
