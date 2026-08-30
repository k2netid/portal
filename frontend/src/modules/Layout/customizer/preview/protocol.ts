/** Query + postMessage contract for Theme Customizer live-preview click-to-select. */

export const CUSTOMIZER_PREVIEW_QUERY = 'ja_customizer_preview';
export const CUSTOMIZER_PARENT_ORIGIN_QUERY = 'ja_parent_origin';

export const MSG_SELECT_TARGET = 'JA_CUSTOMIZER_SELECT_TARGET';
export const MSG_PREVIEW_READY = 'JA_CUSTOMIZER_PREVIEW_READY';
export const MSG_FOCUS_TARGET = 'JA_CUSTOMIZER_FOCUS_TARGET';

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

/** Append preview flags to a public site URL for the customizer iframe. */
export function withCustomizerPreviewParams(url: string, parentOrigin: string): string {
  try {
    const u = new URL(url, typeof window !== 'undefined' ? window.location.origin : 'http://localhost');
    u.searchParams.set(CUSTOMIZER_PREVIEW_QUERY, '1');
    u.searchParams.set(CUSTOMIZER_PARENT_ORIGIN_QUERY, parentOrigin);
    return u.toString();
  } catch {
    const sep = url.includes('?') ? '&' : '?';
    return `${url}${sep}${CUSTOMIZER_PREVIEW_QUERY}=1&${CUSTOMIZER_PARENT_ORIGIN_QUERY}=${encodeURIComponent(parentOrigin)}`;
  }
}

export function isCustomizerSelectTargetMessage(data: unknown): data is CustomizerSelectTargetMessage {
  if (!data || typeof data !== 'object') return false;
  const msg = data as Record<string, unknown>;
  return msg.type === MSG_SELECT_TARGET && typeof msg.target === 'string' && msg.target.length > 0;
}
