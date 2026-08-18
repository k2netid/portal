import type { Theme } from '@/modules/Content/Layout/composables/useTheme'

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
  const file = normalizedKey.slice(normalizedKey.lastIndexOf('/') + 1)
  if (!file.endsWith('.vue')) return null
  return file.slice(0, -4)
}

export const buildThemeViewResolveCandidates = (theme: Theme | null | undefined): string[] => {
  if (!theme) return []

  const slug =
    typeof theme.slug === 'string' && theme.slug.trim() !== ''
      ? theme.slug.trim()
      : ''
  const parentSlug =
    typeof theme.parent_theme === 'string' && theme.parent_theme.trim() !== ''
      ? theme.parent_theme.trim()
      : ''

  if (!slug && !parentSlug) return []
  if (parentSlug && parentSlug !== slug) return [slug, parentSlug].filter(Boolean)
  return [slug].filter(Boolean)
}

export const findThemeViewKey = (
  viewModules: ThemeViewModules,
  themeSlugs: string[],
  pageName: string,
): string | undefined => {
  if (themeSlugs.length === 0) return undefined

  const pageBase = themePageBaseName(pageName)

  for (const slug of themeSlugs) {
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
    const file = k.slice(k.lastIndexOf('/') + 1)
    if (!file.endsWith('.vue')) return false
    return file.slice(0, -4) === pageBase
  })
}
