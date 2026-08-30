import type { Theme } from '@/modules/Layout/composables/useTheme'

export type ThemeViewModules = Record<string, () => Promise<unknown>>

const normalizeGlobKey = (key: string): string => key.toLowerCase().replace(/\\/g, '/')

/** Last path segment, case-insensitive (e.g. "Solusi", "components/Header" → "header"). */
export const themePageBaseName = (pageName: string): string => {
  const segment = pageName.split('/').filter(Boolean).pop() ?? pageName
  return segment.toLowerCase()
}

const fileBaseFromThemePagesPath = (normalizedKey: string, slugLower: string): string | null => {
  const marker = `/themes/${slugLower}/pages/`
  const idx = normalizedKey.indexOf(marker)
  if (idx === -1) return null
  const file = normalizedKey.slice(idx + marker.length)
  if (!file.endsWith('.vue')) return null
  return file.slice(0, -4)
}

const fileBaseFromThemeRootPath = (normalizedKey: string, slugLower: string): string | null => {
  const marker = `/themes/${slugLower}/`
  const idx = normalizedKey.indexOf(marker)
  if (idx === -1) return null
  const rest = normalizedKey.slice(idx + marker.length)
  if (rest.includes('/')) return null
  if (!rest.endsWith('.vue')) return null
  return rest.slice(0, -4)
}

const fileBaseFromThemeComponentsPath = (normalizedKey: string, slugLower: string): string | null => {
  const marker = `/themes/${slugLower}/components/`
  const idx = normalizedKey.indexOf(marker)
  if (idx === -1) return null
  const file = normalizedKey.slice(normalizedKey.lastIndexOf('/') + 1).split('?')[0] ?? ''
  if (!file.endsWith('.vue')) return null
  return file.slice(0, -4)
}

/** In-tree themes used when the API row has no slug or no active theme. Janari first = CMS reference. */
export const BUNDLED_FRONTEND_THEME_SLUGS = ['janari', 'zenith'] as const

export const withBundledThemeFallbacks = (slugs: string[]): string[] => {
  const seen = new Set<string>()
  const out: string[] = []
  for (const raw of [...slugs, ...BUNDLED_FRONTEND_THEME_SLUGS]) {
    const slug = raw.trim()
    if (!slug) continue
    const key = slug.toLowerCase()
    if (seen.has(key)) continue
    seen.add(key)
    out.push(slug)
  }
  return out
}

export const buildThemeViewResolveCandidates = (theme: Theme | null | undefined): string[] => {
  if (!theme) return withBundledThemeFallbacks([])

  const slug =
    typeof theme.slug === 'string' && theme.slug.trim() !== ''
      ? theme.slug.trim()
      : ''
  const parentSlug =
    typeof theme.parent_theme === 'string' && theme.parent_theme.trim() !== ''
      ? theme.parent_theme.trim()
      : ''

  return withBundledThemeFallbacks([slug, parentSlug].filter(Boolean))
}

export const findThemeViewKey = (
  viewModules: ThemeViewModules,
  themeSlugs: string[],
  pageName: string,
): string | undefined => {
  const slugs = themeSlugs.length > 0 ? themeSlugs : [...BUNDLED_FRONTEND_THEME_SLUGS]
  const pageBase = themePageBaseName(pageName)

  for (const slug of slugs) {
    const slugLower = slug.toLowerCase()

    const found = Object.keys(viewModules).find((key) => {
      const k = normalizeGlobKey(key)

      const pagesBase = fileBaseFromThemePagesPath(k, slugLower)
      if (pagesBase !== null && pagesBase === pageBase) return true

      const rootBase = fileBaseFromThemeRootPath(k, slugLower)
      if (rootBase !== null && rootBase === pageBase) return true

      const componentBase = fileBaseFromThemeComponentsPath(k, slugLower)
      if (componentBase !== null && componentBase === pageBase) return true

      return false
    })

    if (found) return found
  }

  // Last resort: any theme package file whose basename matches (case-insensitive).
  return Object.keys(viewModules).find((key) => {
    const k = normalizeGlobKey(key)
    const file = k.slice(k.lastIndexOf('/') + 1).split('?')[0] ?? ''
    if (!file.endsWith('.vue')) return false
    return file.slice(0, -4) === pageBase
  })
}
