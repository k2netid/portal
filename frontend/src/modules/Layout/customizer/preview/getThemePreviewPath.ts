import { resolvePublicEmbedUrl } from '@/modules/Layout/utils/publicSiteUrl';

/** Customizer sidebar item id → public path for live preview iframe. */
const PREVIEW_PATH_BY_ITEM: Record<string, string> = {
  'page-hero': '/',
  'page-about': '/about',
  'page-achievements': '/achievement',
  'page-careers': '/career',
  'page-news': '/blog',
  'page-search': '/search',
  'page-services': '/services',
  'page-contact': '/contact',
  'page-pricing': '/pricing',
  'page-solusi': '/solusi',
  'page-tim': '/tim',
  'page-cms': '/',
  'page-management': '/',
  // Home sections + chrome → apex home (focus scrolls in iframe)
  'page-products': '/',
  'page-partners': '/',
  'page-cta': '/',
  'page-testimonials': '/',
  'page-updates': '/',
  'design-branding': '/',
  'design-colors': '/',
  'design-typo': '/',
  'ux-footer': '/',
  'ux-layout': '/',
  'ux-motion': '/',
  'identity-general': '/',
  'identity-site-profile': '/',
  'identity-menus': '/',
  'item-header': '/',
  'identity-social': '/',
  'comp-hero': '/',
  'comp-isp-bento': '/',
  'comp-packages': '/',
  'comp-footer': '/',
  'comp-partners': '/',
  'comp-testimonials': '/',
  'comp-cta': '/',
  'comp-careers': '/career',
  'comp-achievements': '/achievement',
};

/** Section focus key for scroll/highlight inside preview (data-ja-customizer-target). */
const PREVIEW_FOCUS_BY_ITEM: Record<string, string> = {
  'page-hero': 'hero',
  'page-products': 'products',
  'page-partners': 'partners',
  'page-cta': 'cta',
  'page-testimonials': 'testimonials',
  'page-updates': 'updates',
  'design-branding': 'header',
  'identity-site-profile': 'header',
  'identity-general': 'header',
  'identity-menus': 'nav',
  'item-header': 'header',
  'ux-layout': 'header',
  'ux-footer': 'footer',
  'comp-hero': 'hero',
  'comp-isp-bento': 'bento',
  'comp-packages': 'packages',
  'comp-footer': 'footer',
  'comp-partners': 'partners',
  'comp-testimonials': 'testimonials',
  'comp-cta': 'cta',
  'page-contact': 'contact',
  'page-about': 'about',
  'page-solusi': 'solusi',
  'page-tim': 'tim',
  'page-pricing': 'pricing',
  'page-news': 'news',
  'page-careers': 'careers',
  'page-achievements': 'achievements',
  'page-search': 'search',
  'page-services': 'services',
};

export function resolveCustomizerPreviewPath(itemId: string | null | undefined): string {
  if (!itemId) return '/';
  return PREVIEW_PATH_BY_ITEM[itemId] || '/';
}

export function resolveCustomizerPreviewFocus(itemId: string | null | undefined): string | null {
  if (!itemId) return null;
  return PREVIEW_FOCUS_BY_ITEM[itemId] || null;
}

export function resolveCustomizerPreviewEmbedUrl(itemId: string | null | undefined): string {
  return resolvePublicEmbedUrl(resolveCustomizerPreviewPath(itemId));
}
