const DEFAULT_FAVICON = '/favicon.ico';
export const CONSOLE_FAVICON_STORAGE_KEY = 'ja_console_favicon_href';
export const SITE_FAVICON_STORAGE_KEY = 'ja_site_favicon_href';
export const FAVICON_STORAGE_KEY = 'ja_favicon_href';

let lastAppliedFavicon = '';

export function isConsoleRoute(): boolean {
    if (typeof window === 'undefined') return false;
    const path = window.location.pathname;
    return path.startsWith('/dash') || path.startsWith('/ja-dash') || path.startsWith('/auth') || path.startsWith('/login') || path.startsWith('/setup') || path.startsWith('/manage');
}

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

export function resolveFavicon(candidates: unknown[], options?: { preferFirst?: boolean }): string {
    let generic = '';
    for (const candidate of candidates) {
        const trimmed = asHref(candidate);
        if (!trimmed) continue;
        if (options?.preferFirst) {
            return trimmed;
        }
        if (isGenericEngineFavicon(trimmed)) {
            if (!generic) generic = trimmed;
            continue;
        }
        return trimmed;
    }
    return generic || DEFAULT_FAVICON;
}

export function clearFaviconCache(): void {
    lastAppliedFavicon = '';
    try {
        localStorage.removeItem(CONSOLE_FAVICON_STORAGE_KEY);
        localStorage.removeItem(SITE_FAVICON_STORAGE_KEY);
        localStorage.removeItem(FAVICON_STORAGE_KEY);
    } catch {
        /* private mode */
    }
}

export function applyFavicon(
    href: unknown,
    options?: { allowGeneric?: boolean; force?: boolean; scope?: 'console' | 'site' | 'auto' },
): void {
    if (typeof document === 'undefined' || !document.head) return;

    const normalizedHref = asHref(href);
    if (!normalizedHref) return;

    const isGeneric = isGenericEngineFavicon(normalizedHref);
    if (isGeneric && !options?.allowGeneric) {
        return;
    }

    const isConsole = options?.scope === 'console' || (options?.scope !== 'site' && isConsoleRoute());
    const storageKey = isConsole ? CONSOLE_FAVICON_STORAGE_KEY : SITE_FAVICON_STORAGE_KEY;

    // Check if DOM already has this icon to prevent racing DOM tear-down and re-fetch
    const existing = document.head.querySelectorAll(
        'link[rel="icon"], link[rel="shortcut icon"], link[rel="apple-touch-icon"]',
    );
    const firstLink = existing[0] as HTMLLinkElement | undefined;
    const currentHref = firstLink?.getAttribute('href') || '';

    if (!options?.force && currentHref) {
        try {
            const currentPath = new URL(currentHref, window.location.origin).pathname;
            const targetPath = new URL(normalizedHref, window.location.origin).pathname;
            if (currentPath === targetPath && (lastAppliedFavicon === normalizedHref || !lastAppliedFavicon)) {
                lastAppliedFavicon = normalizedHref;
                try {
                    if (!isGeneric) localStorage.setItem(storageKey, normalizedHref);
                    else localStorage.removeItem(storageKey);
                    localStorage.removeItem(FAVICON_STORAGE_KEY);
                } catch {
                    /* private mode */
                }
                return;
            }
        } catch {
            if (currentHref === normalizedHref) {
                lastAppliedFavicon = normalizedHref;
                return;
            }
        }
    }

    if (!options?.force && lastAppliedFavicon === normalizedHref && existing.length > 0) return;

    existing.forEach((el) => el.remove());

    // Cache-busting parameter forces browsers to reload tab icon immediately ONLY when forced
    const cacheBustedHref = options?.force
        ? (isGeneric
            ? `${DEFAULT_FAVICON}?v=core_${Date.now()}`
            : (normalizedHref.includes('?') ? `${normalizedHref}&v=${Date.now()}` : `${normalizedHref}?v=${Date.now()}`))
        : normalizedHref;

    const rels = ['icon', 'shortcut icon', 'apple-touch-icon'];
    rels.forEach((rel) => {
        const link = document.createElement('link');
        link.rel = rel;
        link.href = cacheBustedHref;
        document.head.appendChild(link);
    });

    lastAppliedFavicon = normalizedHref;
    try {
        if (!isGeneric) {
            localStorage.setItem(storageKey, normalizedHref);
        } else {
            localStorage.removeItem(storageKey);
        }
        localStorage.removeItem(FAVICON_STORAGE_KEY);
    } catch {
        /* private mode */
    }
}
