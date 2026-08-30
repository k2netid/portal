/**
 * Cross-tab / cross-shell sync when the public frontend theme is activated.
 * Console + public share the same origin (sessionStorage), so a stale
 * `frontend_theme_snapshot_v1` can keep showing the previous package after activate.
 */

export const FRONTEND_THEME_SNAPSHOT_KEY = 'frontend_theme_snapshot_v1';
export const FRONTEND_THEME_ACTIVATION_REV_KEY = 'ja_frontend_theme_rev';

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
