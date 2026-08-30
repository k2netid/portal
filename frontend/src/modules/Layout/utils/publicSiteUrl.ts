/**
 * Resolve absolute URLs for the public (apex) site — not the console SPA.
 */

export function resolvePublicSitePath(slug?: string | null): string {
  if (!slug) return '/'
  const clean = String(slug).replace(/^\/+/, '').trim()
  if (!clean || clean === 'home' || clean === 'index') return '/'
  return `/${clean}`
}

export function resolvePublicSiteOrigin(): string {
  const envBase = String(
    import.meta.env.VITE_PUBLIC_SITE_URL
    || import.meta.env.VITE_PORTAL_URL
    || '',
  ).replace(/\/$/, '')
  if (envBase) return envBase
  if (typeof window !== 'undefined' && window.location?.origin) {
    return window.location.origin
  }
  return ''
}

/** Absolute public page URL (origin + path). Falls back to path-only if origin unknown. */
export function resolvePublicSiteUrl(slugOrPath?: string | null): string {
  const path = slugOrPath && String(slugOrPath).startsWith('/')
    ? String(slugOrPath)
    : resolvePublicSitePath(slugOrPath)
  const origin = resolvePublicSiteOrigin()
  if (!origin) return path
  return path === '/' ? `${origin}/` : `${origin}${path}`
}
