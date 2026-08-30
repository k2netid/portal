/** Query + postMessage contract for Theme Customizer live-preview click-to-select. */

export const CUSTOMIZER_PREVIEW_QUERY = 'ja_customizer_preview';
export const CUSTOMIZER_PARENT_ORIGIN_QUERY = 'ja_parent_origin';
export const CUSTOMIZER_THEME_SLUG_QUERY = 'ja_theme_slug';
export const CUSTOMIZER_PREVIEW_THEME_STORAGE = 'ja_customizer_preview_theme';

export const MSG_SELECT_TARGET = 'JA_CUSTOMIZER_SELECT_TARGET';
export const MSG_PREVIEW_READY = 'JA_CUSTOMIZER_PREVIEW_READY';
export const MSG_FOCUS_TARGET = 'JA_CUSTOMIZER_FOCUS_TARGET';
export const MSG_THEME_BOOT = 'JA_CUSTOMIZER_THEME_BOOT';

export type CustomizerPreviewMode = 'design' | 'bindings';

export interface CustomizerSelectTargetMessage {
  type: typeof MSG_SELECT_TARGET;
  target: string;
  mode?: CustomizerPreviewMode;
}

export interface CustomizerPreviewReadyMessage {
  type: typeof MSG_PREVIEW_READY;
}

export interface CustomizerFocusTargetMessage {
  type: typeof MSG_FOCUS_TARGET;
  target: string;
}

export interface CustomizerThemeBootMessage {
  type: typeof MSG_THEME_BOOT;
  theme: Record<string, unknown>;
}

export interface CustomizerPreviewTargetConfig {
  /** Sidebar nav item id (e.g. page-partners, ux-footer, identity-menus). */
  navItemId: string;
  /** Default organization mode when opening from preview. */
  mode?: CustomizerPreviewMode;
  /** Optional Content-mode component id (opens `comp-${id}`). */
  bindingsId?: string;
}

export function isCustomizerPreviewQuery(search: string): boolean {
  try {
    return new URLSearchParams(search).get(CUSTOMIZER_PREVIEW_QUERY) === '1';
  } catch {
    return false;
  }
}

export function readParentOriginFromQuery(search: string): string | null {
  try {
    const raw = new URLSearchParams(search).get(CUSTOMIZER_PARENT_ORIGIN_QUERY);
    if (!raw) return null;
    const origin = decodeURIComponent(raw);
    if (!/^https?:\/\//i.test(origin)) return null;
    return origin;
  } catch {
    return null;
  }
}

export function readThemeSlugFromQuery(search: string): string | null {
  try {
    const raw = new URLSearchParams(search).get(CUSTOMIZER_THEME_SLUG_QUERY);
    if (!raw) return null;
    const slug = decodeURIComponent(raw).trim().toLowerCase();
    return /^[a-z0-9][a-z0-9_-]{0,63}$/.test(slug) ? slug : null;
  } catch {
    return null;
  }
}

/** Append preview flags to a public site URL for the customizer iframe. */
export function withCustomizerPreviewParams(
  url: string,
  parentOrigin: string,
  themeSlug?: string | null,
): string {
  try {
    const u = new URL(url, typeof window !== 'undefined' ? window.location.origin : 'http://localhost');
    u.searchParams.set(CUSTOMIZER_PREVIEW_QUERY, '1');
    u.searchParams.set(CUSTOMIZER_PARENT_ORIGIN_QUERY, parentOrigin);
    if (themeSlug) {
      u.searchParams.set(CUSTOMIZER_THEME_SLUG_QUERY, themeSlug);
    }
    return u.toString();
  } catch {
    const sep = url.includes('?') ? '&' : '?';
    const slugPart = themeSlug
      ? `&${CUSTOMIZER_THEME_SLUG_QUERY}=${encodeURIComponent(themeSlug)}`
      : '';
    return `${url}${sep}${CUSTOMIZER_PREVIEW_QUERY}=1&${CUSTOMIZER_PARENT_ORIGIN_QUERY}=${encodeURIComponent(parentOrigin)}${slugPart}`;
  }
}

export function isCustomizerSelectTargetMessage(data: unknown): data is CustomizerSelectTargetMessage {
  if (!data || typeof data !== 'object') return false;
  const msg = data as Record<string, unknown>;
  return msg.type === MSG_SELECT_TARGET && typeof msg.target === 'string' && msg.target.length > 0;
}

export function isCustomizerThemeBootMessage(data: unknown): data is CustomizerThemeBootMessage {
  if (!data || typeof data !== 'object') return false;
  const msg = data as Record<string, unknown>;
  return msg.type === MSG_THEME_BOOT && !!msg.theme && typeof msg.theme === 'object';
}

export function readStoredPreviewTheme(expectedSlug?: string | null): Record<string, unknown> | null {
  if (typeof sessionStorage === 'undefined') return null;
  try {
    const raw = sessionStorage.getItem(CUSTOMIZER_PREVIEW_THEME_STORAGE);
    if (!raw) return null;
    const parsed = JSON.parse(raw) as Record<string, unknown>;
    const slug = typeof parsed.slug === 'string' ? parsed.slug.toLowerCase() : '';
    if (expectedSlug && slug && slug !== expectedSlug.toLowerCase()) return null;
    return parsed;
  } catch {
    return null;
  }
}

export function storePreviewTheme(theme: Record<string, unknown>): void {
  if (typeof sessionStorage === 'undefined') return;
  try {
    sessionStorage.setItem(CUSTOMIZER_PREVIEW_THEME_STORAGE, JSON.stringify(theme));
  } catch {
    /* quota / private mode */
  }
}
