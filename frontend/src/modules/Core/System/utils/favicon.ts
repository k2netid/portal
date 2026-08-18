const DEFAULT_FAVICON = '/favicon.ico';

let lastAppliedFavicon = '';

function normalizeHref(href: unknown): string {
    if (typeof href !== 'string') return DEFAULT_FAVICON;
    const trimmed = href.trim();
    return trimmed.length > 0 ? trimmed : DEFAULT_FAVICON;
}

export function resolveFavicon(candidates: unknown[]): string {
    for (const candidate of candidates) {
        if (typeof candidate === 'string' && candidate.trim() !== '') {
            return candidate.trim();
        }
    }
    return DEFAULT_FAVICON;
}

export function applyFavicon(href: unknown): void {
    if (typeof document === 'undefined' || !document.head) return;

    const normalizedHref = normalizeHref(href);
    if (lastAppliedFavicon === normalizedHref) return;

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
}
