/**
 * Cross-tab / cross-shell sync when the public frontend theme is activated.
 * Console + public share the same origin (sessionStorage), so a stale
 * `frontend_theme_snapshot_v1` can keep showing the previous package after activate.
 */

export const FRONTEND_THEME_SNAPSHOT_KEY = 'frontend_theme_snapshot_v1';
export const FRONTEND_THEME_ACTIVATION_REV_KEY = 'ja_frontend_theme_rev';
export const THEME_CARD_EMBED_QUERY = 'theme-card';

export function isThemeCardEmbedPreview(search?: string): boolean {
    if (typeof window === 'undefined') return false;
    const raw = search ?? window.location.search;
    return new URLSearchParams(raw).get('_embed') === THEME_CARD_EMBED_QUERY;
}

export function readFrontendThemeActivationRev(): { at: number; slug: string | null } | null {
    if (typeof window === 'undefined') return null;
    try {
        const raw = window.localStorage.getItem(FRONTEND_THEME_ACTIVATION_REV_KEY);
        if (!raw) return null;
        const parsed = JSON.parse(raw) as { at?: number; slug?: string | null };
        return {
            at: Number.isFinite(parsed.at) ? Number(parsed.at) : 0,
            slug: typeof parsed.slug === 'string' ? parsed.slug : null,
        };
    } catch {
        return null;
    }
}

export function snapshotMatchesActivationRev(activeSlug?: string | null): boolean {
    const rev = readFrontendThemeActivationRev();
    if (!rev?.slug) return true;
    const current = typeof activeSlug === 'string' ? activeSlug.trim().toLowerCase() : '';
    if (!current) return false;
    return current === rev.slug.trim().toLowerCase();
}

export function buildThemeCardPreviewUrl(rev: number | string): string {
    const base = typeof window !== 'undefined' && window.location?.origin
        ? `${window.location.origin}/`
        : '/';
    const url = new URL(base, typeof window !== 'undefined' ? window.location.origin : 'http://localhost');
    url.searchParams.set('_embed', THEME_CARD_EMBED_QUERY);
    url.searchParams.set('_ft', String(rev));
    return `${url.pathname}${url.search}`;
}

export function clearFrontendThemeSnapshot(): void {
    if (typeof window === 'undefined') return;
    try {
        window.sessionStorage.removeItem(FRONTEND_THEME_SNAPSHOT_KEY);
    } catch {
        /* ignore quota / privacy mode */
    }
}

/** Call after a successful POST …/themes/{slug}/activate. */
export function notifyFrontendThemeActivated(slug?: string | null): void {
    if (typeof window === 'undefined') return;
    clearFrontendThemeSnapshot();
    try {
        window.localStorage.setItem(
            FRONTEND_THEME_ACTIVATION_REV_KEY,
            JSON.stringify({ at: Date.now(), slug: slug ?? null }),
        );
    } catch {
        /* ignore */
    }
    try {
        window.dispatchEvent(
            new CustomEvent('ja-frontend-theme-activated', {
                detail: { slug: slug ?? null },
            }),
        );
    } catch {
        /* ignore */
    }
}
