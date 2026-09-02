export type LayungTranslate = (key: string, fallback: string) => string;

const PAGE_SEO: Record<string, { titleKey: string; titleFallback: string; descKey: string; descFallback: string }> = {
  'pages/Home': {
    titleKey: 'header.home',
    titleFallback: 'Beranda',
    descKey: 'footer.description',
    descFallback: 'K2NET — ISP, managed services, dan penyedia produk IT.',
  },
  'pages/About': {
    titleKey: 'pages.about.title',
    titleFallback: 'Tentang Kami',
    descKey: 'pages.about.subtitle',
    descFallback: 'Merek operasional PT Kirana Karina Network.',
  },
  'pages/Contact': {
    titleKey: 'pages.contact.title',
    titleFallback: 'Kontak',
    descKey: 'pages.contact.subtitle',
    descFallback: 'Sales, CS, NOC, dan Service Desk K2NET.',
  },
  'pages/Services': {
    titleKey: 'header.services',
    titleFallback: 'Internet',
    descKey: 'bento.ispDesc',
    descFallback: 'Dedicated Internet, Broadband Bisnis SOHO, dan Retail Broadband.',
  },
  'pages/Solusi': {
    titleKey: 'header.solusi',
    titleFallback: 'Managed Services',
    descKey: 'bento.mspDesc',
    descFallback: 'Dukungan IT operasional untuk sekolah dan institusi.',
  },
  'pages/Pricing': {
    titleKey: 'header.pricing',
    titleFallback: 'Paket & Harga',
    descKey: 'packages.hubSubtitle',
    descFallback: 'Paket konektivitas internet (ISP) atau layanan IT terkelola (MSP).',
  },
  'pages/PricingIsp': {
    titleKey: 'header.pricingIsp',
    titleFallback: 'Paket Internet',
    descKey: 'pricingIsp.retailSubtitle',
    descFallback: 'Paket internet rumah tangga, SOHO, dan dedicated.',
  },
  'pages/PricingMsp': {
    titleKey: 'header.pricingMsp',
    titleFallback: 'Paket MSP',
    descKey: 'pricingMsp.audience',
    descFallback: 'Managed services untuk sekolah dan institusi.',
  },
  'pages/Achievement': {
    titleKey: 'header.achievement',
    titleFallback: 'SLA',
    descKey: 'footer.description',
    descFallback: 'SLA dan sertifikasi operasional K2NET.',
  },
  'pages/CareerCenter': {
    titleKey: 'header.career',
    titleFallback: 'Karir',
    descKey: 'footer.description',
    descFallback: 'Lowongan dan karir di K2NET.',
  },
  'pages/Tim': {
    titleKey: 'header.tim',
    titleFallback: 'Tim',
    descKey: 'footer.description',
    descFallback: 'Tim operasional K2NET.',
  },
  'pages/Blog': {
    titleKey: 'header.blog',
    titleFallback: 'Berita',
    descKey: 'footer.description',
    descFallback: 'Berita dan warta K2NET.',
  },
  'pages/Search': {
    titleKey: 'header.search',
    titleFallback: 'Pencarian',
    descKey: 'footer.description',
    descFallback: 'Cari halaman di situs K2NET.',
  },
};

export function resolveLayungPublicSeo(input: {
  themePage?: string;
  siteName: string;
  t: LayungTranslate;
}): { title: string; description: string } {
  const siteName = input.siteName.trim() || 'K2NET';
  const copy = input.themePage ? PAGE_SEO[input.themePage] : undefined;
  const pageTitle = copy ? input.t(copy.titleKey, copy.titleFallback) : siteName;
  const description = copy
    ? input.t(copy.descKey, copy.descFallback)
    : input.t('footer.description', 'K2NET — ISP, managed services, dan penyedia produk IT.');

  const title =
    !input.themePage || input.themePage === 'pages/Home'
      ? siteName
      : `${pageTitle} · ${siteName}`;

  return { title, description };
}
