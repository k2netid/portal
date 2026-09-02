import { lookupModuleLocale, looksLikeI18nPath } from '@/modules/Layout/customizer/i18n/lookupModuleLocale';

function humanizeTail(key: string): string {
    const tail = key.split('.').pop() || key;
    return tail.replace(/_/g, ' ');
}

/**
 * Customizer sidebar labels must never leak raw i18n paths.
 * vue-i18n can miss `theme.<slug>.*` when the active locale catalog is still loading;
 * fall back to the static theme/module JSON packs.
 */
export function translateCustomizerNavKey(
    t: (key: string, ...args: unknown[]) => string,
    key: string,
    locale = 'id',
): string {
    if (!key) return '';

    const fromPack = lookupModuleLocale(locale, key);
    if (fromPack) return fromPack;

    try {
        const value = t(key);
        if (typeof value === 'string' && value && value !== key && !looksLikeI18nPath(value)) {
            return value;
        }
    } catch {
        // vue-i18n throws on some missing nested paths in composition mode
    }

    const publishingKey = key.replace(
        /^theme\.[^.]+\.customizer\.(sidebar|items)\./,
        (_match, segment: string) =>
            segment === 'sidebar'
                ? 'publishing.theme_customizer.sidebar.items.'
                : 'publishing.theme_customizer.items.',
    );
    if (publishingKey !== key) {
        const fromPublishing = lookupModuleLocale(locale, publishingKey);
        if (fromPublishing) return fromPublishing;
        try {
            const value = t(publishingKey);
            if (typeof value === 'string' && value && value !== publishingKey && !looksLikeI18nPath(value)) {
                return value;
            }
        } catch {
            // ignore
        }
    }

    return humanizeTail(key);
}
