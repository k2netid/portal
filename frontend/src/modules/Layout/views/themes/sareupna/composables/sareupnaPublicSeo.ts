export type SareupnaTranslate = (key: string, fallback?: string) => string;

const PAGE_SEO: Record<string, { titleKey: string; titleFallback: string; descKey: string; descFallback: string }> = {
  'pages/Home': {
    titleKey: 'header.home',
    titleFallback: 'Beranda',
    descKey: 'footer.description',
    descFallback: 'Jejakawan — Membangun Solusi, Meninggalkan Jejak. Supported by K2NET.',
  },
  'pages/About': {
    titleKey: 'header.about',
    titleFallback: 'Tentang Jejakawan',
    descKey: 'pages.about.subtitle',
    descFallback: 'Filosofi dan ekosistem platform Jejakawan — Membangun Solusi, Meninggalkan Jejak.',
  },
  'pages/Solutions': {
    titleKey: 'pages.solutions.title',
    titleFallback: 'Solusi Cloud',
    descKey: 'pages.solutions.subtitle',
    descFallback: 'Infrastruktur terdistribusi dan solusi rekayasa platform.',
  },
  'pages/Pricing': {
    titleKey: 'pages.pricing.title',
    titleFallback: 'Paket & Harga',
    descKey: 'pages.pricing.subtitle',
    descFallback: 'Paket platform cloud dan lisensi modular.',
  },
  'pages/Blog': {
    titleKey: 'header.blog',
    titleFallback: 'Wawasan',
    descKey: 'pages.blog.subtitle',
    descFallback: 'Artikel teknis, arsitektur sistem, dan inovasi cloud.',
  },
  'pages/Contact': {
    titleKey: 'header.contact',
    titleFallback: 'Kontak',
    descKey: 'pages.contact.introQuestion',
    descFallback: 'Hubungi tim arsitek cloud untuk konsultasi platform.',
  },
  'pages/Search': {
    titleKey: 'header.search',
    titleFallback: 'Pencarian',
    descKey: 'footer.description',
    descFallback: 'Cari dokumentasi dan konten platform.',
  },
};

export function resolveSareupnaPublicSeo(input: {
  themePage?: string;
  siteName: string;
  t: SareupnaTranslate;
}): { title: string; description: string } {
  const siteName = input.siteName.trim() || 'Portal';
  const copy = input.themePage ? PAGE_SEO[input.themePage] : undefined;
  const pageTitle = copy ? input.t(copy.titleKey, copy.titleFallback) : siteName;
  const description = copy
    ? input.t(copy.descKey, copy.descFallback)
    : input.t('footer.description', `${siteName} — Membangun Solusi, Meninggalkan Jejak. Supported by K2NET.`);

  const title =
    !input.themePage || input.themePage === 'pages/Home'
      ? siteName
      : `${pageTitle} · ${siteName}`;

  return { title, description };
}
