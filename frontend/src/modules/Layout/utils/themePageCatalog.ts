import type { RouteRecordRaw } from 'vue-router'
import { publicRoutes } from '@/engine/router/public'

export type ThemePageCatalogItem = {
  id: string
  title: string
  slug: string
  type: 'page'
  isThemeTemplate: true
  status: 'published'
  themePage: string
}

const TITLE_BY_THEME_PAGE: Record<string, string> = {
  'pages/Home': 'Beranda (Home)',
  'pages/About': 'Tentang Kami (About)',
  'pages/Tim': 'Tim Kami (Team)',
  'pages/Pricing': 'Harga & Paket (Pricing)',
  'pages/Solusi': 'Produk & Solusi',
  'pages/Services': 'Layanan (Services)',
  'pages/Contact': 'Hubungi Kami (Contact)',
  'pages/Blog': 'Arsip Berita (Blog)',
  'pages/CareerCenter': 'Pusat Karier (Careers)',
  'pages/Achievement': 'Sorotan & Prestasi',
  'pages/Search': 'Pencarian (Search)',
}

const themePageVueModules = import.meta.glob('@/modules/Layout/views/themes/*/pages/*.vue')

function themePageHasVue(themePage: string): boolean {
  const suffix = `/${themePage}.vue`
  return Object.keys(themePageVueModules).some((key) => key.replace(/\\/g, '/').endsWith(suffix))
}

function humanizeThemePage(themePage: string): string {
  const leaf = themePage.split('/').pop() || themePage
  return leaf.replace(/([a-z])([A-Z])/g, '$1 $2')
}

function routeSlug(path: string): string {
  if (!path || path === '') return 'home'
  return path.replace(/^\/+/, '').split('/')[0] || 'home'
}

/** Theme page catalog derived from public router (single source of truth). */
export function getPublicThemePageCatalog(): ThemePageCatalogItem[] {
  const root = publicRoutes.find((r: RouteRecordRaw) => r.path === '/')
  const children = (root?.children || []) as RouteRecordRaw[]
  const seen = new Set<string>()
  const items: ThemePageCatalogItem[] = []

  for (const child of children) {
    const themePage = child.meta?.themePage
    if (typeof themePage !== 'string' || !themePage.startsWith('pages/')) continue
    // Skip dynamic post/page shells
    if (String(child.path || '').includes(':')) continue
    if (themePage === 'pages/Post' || themePage === 'pages/Page') continue
    // Skip routes without a Vue page in any bundled theme (e.g. services stub)
    if (!themePageHasVue(themePage)) continue

    const slug = routeSlug(String(child.path || ''))
    if (seen.has(themePage) || seen.has(slug)) continue
    seen.add(themePage)
    seen.add(slug)

    items.push({
      id: `theme-${slug}`,
      title: TITLE_BY_THEME_PAGE[themePage] || humanizeThemePage(themePage),
      slug,
      type: 'page',
      isThemeTemplate: true,
      status: 'published',
      themePage,
    })
  }

  return items
}
