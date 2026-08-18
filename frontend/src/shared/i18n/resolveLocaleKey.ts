type ExistsFn = (key: string) => boolean;

/**
 * Resolve a locale key without leaking raw dotted paths into the UI.
 * Prefer route/nav labelKey + this helper everywhere user-visible strings are built.
 */
export function resolveLocaleKey(
    t: (key: string) => string,
    te: ExistsFn,
    key: string,
    fallback?: string,
): string {
    if (!key) return fallback ?? '';
    if (te(key)) return t(key);

    if (fallback) return fallback;

    const last = key.split('.').pop() ?? key;
    return last
        .replace(/([a-z])([A-Z])/g, '$1 $2')
        .replace(/-/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
}
