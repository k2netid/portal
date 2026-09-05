export type JanariTranslate = (key: string, fallback?: string) => string;

const PAGE_SEO: Record<string, { titleKey: string; titleFallback: string; descKey: string; descFallback: string }> = {
  'pages/Home': {
    titleKey: 'header.home',
    titleFallback: 'Beranda',
    descKey: 'footer.description',
    descFallback: 'Jejakawan Core Engine — Platform Modern CMS, Publishing, dan Manajemen Tema.',
  },
  'pages/About': {
    titleKey: 'header.about',
    titleFallback: 'Tentang Kami',
    descKey: 'pages.about.heroSubtitle',
    descFallback: 'Mengenal ekosistem, visi, dan solusi inovasi Jejakawan.',
  },
  'pages/Services': {
    titleKey: 'header.services',
    titleFallback: 'Layanan',
    descKey: 'pages.services.subtitle',
    descFallback: 'Solusi teknologi terpadu dan layanan profesional.',
  },
  'pages/Pricing': {
    titleKey: 'header.pricing',
    titleFallback: 'Paket & Harga',
    descKey: 'pages.pricing.subtitle',
    descFallback: 'Pilihan paket transparan dengan skema lisensi fleksibel.',
  },
  'pages/Blog': {
    titleKey: 'header.blog',
    titleFallback: 'Berita & Warta',
    descKey: 'footer.description',
    descFallback: 'Warta terkini, rilis fitur, dan artikel edukatif.',
  },
  'pages/Contact': {
    titleKey: 'header.contact',
    titleFallback: 'Kontak',
    descKey: 'pages.contact.subtitle',
    descFallback: 'Hubungi tim ahli kami untuk konsultasi dan bantuan.',
  },
  'pages/Search': {
    titleKey: 'header.search',
    titleFallback: 'Pencarian',
    descKey: 'footer.description',
    descFallback: 'Pencarian halaman dan konten publik.',
  },
};

export function resolveJanariPublicSeo(input: {
  themePage?: string;
  siteName: string;
  t: JanariTranslate;
}): { title: string; description: string } {
  const siteName = input.siteName.trim() || 'Portal';
  const copy = input.themePage ? PAGE_SEO[input.themePage] : undefined;
  const pageTitle = copy ? input.t(copy.titleKey, copy.titleFallback) : siteName;
  const description = copy
    ? input.t(copy.descKey, copy.descFallback)
    : input.t('footer.description', `${siteName} — Platform Modern CMS, Publishing, dan Manajemen Tema.`);

  const title =
    !input.themePage || input.themePage === 'pages/Home'
      ? siteName
      : `${pageTitle} · ${siteName}`;

  return { title, description };
}
