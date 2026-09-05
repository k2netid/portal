export type SarangengeTranslate = (key: string, fallback?: string) => string;

const PAGE_SEO: Record<string, { titleKey: string; titleFallback: string; descKey: string; descFallback: string }> = {
  'pages/Home': {
    titleKey: 'header.home',
    titleFallback: 'Beranda',
    descKey: 'footer.description',
    descFallback: 'Sekolah Menengah Kejuruan Pusat Keunggulan.',
  },
  'pages/About': {
    titleKey: 'header.about',
    titleFallback: 'Profil Sekolah',
    descKey: 'pages.about.heroSubtitle',
    descFallback: 'Profil, visi misi, dan ekosistem pendidikan sekolah vokasi unggulan.',
  },
  'pages/Programs': {
    titleKey: 'header.solusi',
    titleFallback: 'Program Keahlian',
    descKey: 'pages.programs.subtitle',
    descFallback: 'Kompetensi keahlian dan kurikulum vokasi berstandar industri 4.0.',
  },
  'pages/Solusi': {
    titleKey: 'header.solusi',
    titleFallback: 'Program Keahlian',
    descKey: 'pages.programs.subtitle',
    descFallback: 'Kompetensi keahlian dan kurikulum vokasi berstandar industri 4.0.',
  },
  'pages/Facilities': {
    titleKey: 'header.services',
    titleFallback: 'Fasilitas Kampus',
    descKey: 'pages.facilities.subtitle',
    descFallback: 'Sarana prasarana, bengkel praktik, dan laboratorium modern.',
  },
  'pages/Services': {
    titleKey: 'header.services',
    titleFallback: 'Fasilitas Kampus',
    descKey: 'pages.facilities.subtitle',
    descFallback: 'Sarana prasarana, bengkel praktik, dan laboratorium modern.',
  },
  'pages/Pricing': {
    titleKey: 'header.pricing',
    titleFallback: 'Biaya Pendidikan & PPDB',
    descKey: 'pages.pricing.subtitle',
    descFallback: 'Informasi pembiayaan pendidikan, bantuan operasional, dan beasiswa.',
  },
  'pages/Achievement': {
    titleKey: 'header.achievement',
    titleFallback: 'Prestasi Siswa',
    descKey: 'pages.achievement.subtitle',
    descFallback: 'Galeri capaian medali dan kejuaraan siswa tingkat regional hingga internasional.',
  },
  'pages/CareerCenter': {
    titleKey: 'header.career',
    titleFallback: 'BKK & Alumni',
    descKey: 'pages.alumni.subtitle',
    descFallback: 'Bursa Kerja Khusus, kemitraan industri, dan jejaring alumni sekolah.',
  },
  'pages/Tim': {
    titleKey: 'header.tim',
    titleFallback: 'Guru & Tenaga Kependidikan',
    descKey: 'pages.tim.subtitle',
    descFallback: 'Direktori dewan guru dan tenaga kependidikan bersertifikasi profesional.',
  },
  'pages/Blog': {
    titleKey: 'header.blog',
    titleFallback: 'Warta Sekolah',
    descKey: 'footer.description',
    descFallback: 'Warta, pengumuman, dan agenda kegiatan resmi sekolah.',
  },
  'pages/Contact': {
    titleKey: 'header.contact',
    titleFallback: 'Kontak & PPDB',
    descKey: 'pages.contact.subtitle',
    descFallback: 'Kontak resmi, saluran siaga WhatsApp PPDB, dan alamat lokasi sekolah.',
  },
  'pages/Search': {
    titleKey: 'header.search',
    titleFallback: 'Pencarian Informasi',
    descKey: 'footer.description',
    descFallback: 'Pencarian warta, agenda, dan informasi sekolah.',
  },
};

export function resolveSarangengePublicSeo(input: {
  themePage?: string;
  siteName: string;
  t: SarangengeTranslate;
}): { title: string; description: string } {
  const siteName = input.siteName.trim() || 'Portal Sekolah';
  const copy = input.themePage ? PAGE_SEO[input.themePage] : undefined;
  const pageTitle = copy ? input.t(copy.titleKey, copy.titleFallback) : siteName;
  const description = copy
    ? input.t(copy.descKey, copy.descFallback)
    : input.t('footer.description', `${siteName} — Sekolah Menengah Kejuruan Pusat Keunggulan.`);

  const title =
    !input.themePage || input.themePage === 'pages/Home'
      ? siteName
      : `${pageTitle} · ${siteName}`;

  return { title, description };
}
