/**
 * Shared locale preference: localStorage → browser languages → fallback.
 * Used by console i18n and the Site-off kernel landing shell.
 */

export const normalizeAgainstAvailable = (
    code: string,
    available: readonly string[],
    fallback: string,
): string => {
    const trimmed = (code || '').trim();
    if (!trimmed) {
        return fallback;
    }
    if (available.includes(trimmed)) {
        return trimmed;
    }
    const base = trimmed.split('-')[0]?.toLowerCase() ?? '';
    if (base && available.includes(base)) {
        return base;
    }
    return fallback;
};

export const readBrowserLanguages = (): string[] => {
    if (typeof navigator === 'undefined') {
        return [];
    }
    const list = navigator.languages?.length
        ? [...navigator.languages]
        : [navigator.language || (navigator as unknown as { userLanguage?: string }).userLanguage || ''];

    return list.filter((lang): lang is string => typeof lang === 'string' && lang.trim() !== '');
};

/**
 * Priority: 1) stored preference, 2) browser language list, 3) fallback.
 */
export const resolvePreferredLocale = (
    available: readonly string[],
    options?: {
        stored?: string | null;
        browserLanguages?: readonly string[];
        fallback?: string;
    },
): string => {
    const fallback = options?.fallback && available.includes(options.fallback)
        ? options.fallback
        : (available[0] ?? 'id');

    const stored = options?.stored?.trim();
    if (stored && available.includes(stored)) {
        return stored;
    }

    const browserLanguages = options?.browserLanguages ?? readBrowserLanguages();
    for (const lang of browserLanguages) {
        const normalized = normalizeAgainstAvailable(lang, available, fallback);
        if (available.includes(normalized) && normalized !== fallback) {
            return normalized;
        }
        // Accept fallback when browser explicitly prefers it (e.g. id-ID → id).
        if (normalized === fallback && lang.toLowerCase().startsWith(fallback)) {
            return fallback;
        }
    }

    // First browser hit that maps to any available locale (including fallback).
    for (const lang of browserLanguages) {
        const normalized = normalizeAgainstAvailable(lang, available, fallback);
        if (available.includes(normalized)) {
            return normalized;
        }
    }

    return fallback;
};
