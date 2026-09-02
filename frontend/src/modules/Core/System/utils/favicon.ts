const DEFAULT_FAVICON = '/favicon.ico';
export const FAVICON_STORAGE_KEY = 'ja_favicon_href';

let lastAppliedFavicon = '';

export function isGenericEngineFavicon(href: string): boolean {
    const trimmed = href.trim();
    if (!trimmed) return true;
    try {
        const path = new URL(trimmed, 'https://placeholder.local').pathname.toLowerCase();
        return path === '/favicon.ico' || path.endsWith('/favicon.ico');
    } catch {
        return trimmed === DEFAULT_FAVICON || trimmed.endsWith('/favicon.ico');
    }
}

function asHref(candidate: unknown): string {
    if (typeof candidate === 'string') return candidate.trim();
    if (candidate && typeof candidate === 'object' && 'url' in candidate) {
        const url = (candidate as { url?: unknown }).url;
        return typeof url === 'string' ? url.trim() : '';
    }
    return '';
}

export function resolveFavicon(candidates: unknown[]): string {
    let generic = '';
    for (const candidate of candidates) {
        const trimmed = asHref(candidate);
        if (!trimmed) continue;
        if (isGenericEngineFavicon(trimmed)) {
            if (!generic) generic = trimmed;
            continue;
        }
        return trimmed;
    }
    return generic || DEFAULT_FAVICON;
}

export function applyFavicon(href: unknown, options?: { allowGeneric?: boolean }): void {
    if (typeof document === 'undefined' || !document.head) return;

    const normalizedHref = asHref(href);
    if (!normalizedHref) return;
    if (lastAppliedFavicon === normalizedHref) return;

    // First paint often already has a real icon from PHP inject / localStorage cache.
    // Do not clobber it with /favicon.ico while identity or theme settings are still loading.
    if (isGenericEngineFavicon(normalizedHref) && !options?.allowGeneric) {
        return;
    }

    const existing = document.head.querySelectorAll(
        'link[rel="icon"], link[rel="shortcut icon"], link[rel="apple-touch-icon"]',
    );
    existing.forEach((el) => el.remove());

    const rels = ['icon', 'shortcut icon', 'apple-touch-icon'];
    rels.forEach((rel) => {
        const link = document.createElement('link');
        link.rel = rel;
        link.href = normalizedHref;
        document.head.appendChild(link);
    });

    lastAppliedFavicon = normalizedHref;
    try {
        if (!isGenericEngineFavicon(normalizedHref)) {
            localStorage.setItem(FAVICON_STORAGE_KEY, normalizedHref);
        }
    } catch {
        /* private mode */
    }
}
