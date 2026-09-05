import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';

export function useSarangengeIdentity() {
  const { getSetting } = useTheme();
  const systemStore = useSystemStore();

  const siteSettings = computed(() => {
    return {
      ...((systemStore.settings as Record<string, unknown> | undefined) || {}),
      ...((systemStore.siteSettings as Record<string, unknown> | undefined) || {}),
    };
  });

  const siteLogo = computed(() => {
    // 1. Theme Customizer override
    const fromTheme = getSetting('brand_logo', '') || getSetting('site_logo', '') || getSetting('school_logo', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) {
      return fromTheme.trim();
    }
    // 2. Global Site Settings (general.site_logo)
    const fromSite = (siteSettings.value.site_logo || systemStore.siteSettings?.site_logo) as string | undefined;
    if (fromSite && typeof fromSite === 'string' && fromSite.trim()) {
      return fromSite.trim();
    }
    // 3. Fallback to App Identity logo
    const fromApp = systemStore.appIdentity?.app_logo;
    if (fromApp && typeof fromApp === 'string' && fromApp.trim()) {
      return fromApp.trim();
    }
    return '';
  });

  const displaySchoolName = computed(() => {
    const fromTheme = getSetting('school_name', '') || getSetting('site_title', '') || getSetting('site_name', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    const fromSystem = (siteSettings.value.site_name || systemStore.siteSettings?.site_name) as string | undefined;
    if (fromSystem && typeof fromSystem === 'string' && fromSystem.trim()) return fromSystem.trim();
    return 'Portal Sekolah';
  });

  const displayTagline = computed(() => {
    const fromTheme = getSetting('school_tagline', '') || getSetting('site_tagline', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    const fromSystem = siteSettings.value.site_tagline;
    if (fromSystem && typeof fromSystem === 'string' && fromSystem.trim()) return fromSystem.trim();
    return 'Maju Mandiri Berkarakter — Sekolah Menengah Kejuruan Pusat Keunggulan';
  });

  const displayAddress = computed(() => {
    const fromTheme = getSetting('contact_address', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    return 'Jl. Soekarno-Hatta No. 636, Sekejati, Kec. Buahbatu, Kota Bandung, Jawa Barat 40286';
  });

  const displayPhone = computed(() => {
    const fromTheme = getSetting('contact_phone', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    return '+62 22 7563286';
  });

  const displayEmail = computed(() => {
    const fromTheme = getSetting('contact_email', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    return 'info@smkn6bandung.sch.id';
  });

  const displayAccreditation = computed(() => {
    const fromTheme = getSetting('school_accreditation', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    return 'Akreditasi A (Unggul)';
  });

  const displayNpsn = computed(() => {
    const fromTheme = getSetting('school_npsn', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    return 'NPSN: 20268899';
  });

  const displayPrincipalName = computed(() => {
    const fromTheme = getSetting('school_principal_name', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    return 'Drs. H. Rahmat Sudrajat, M.Pd.';
  });

  const phoneDialHref = computed(() => {
    return `tel:${displayPhone.value.replace(/[^0-9+]/g, '')}`;
  });

  const whatsAppRaw = computed(() => {
    const fromTheme = getSetting('contact_whatsapp', '') || getSetting('whatsapp_hotline', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    return '628127563286';
  });

  const whatsAppUrl = computed(() => {
    const cleaned = whatsAppRaw.value.replace(/[^0-9]/g, '');
    if (!cleaned) return '';
    const text = encodeURIComponent(`Halo Admin PPDB ${displaySchoolName.value}, saya ingin berkonsultasi mengenai pendaftaran siswa baru.`);
    return `https://wa.me/${cleaned}?text=${text}`;
  });

  const isPpdbOpen = computed(() => {
    const raw = getSetting('ppdb_is_open', getSetting('ppdb_open', true));
    return raw !== false && raw !== 'false' && raw !== 0;
  });

  const ppdbPortalUrl = computed(() => {
    const fromTheme = getSetting('ppdb_external_url', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim()) return fromTheme.trim();
    return 'https://ppdb.jabarprov.go.id/';
  });

  return {
    displaySchoolName,
    displayTagline,
    displayAddress,
    displayPhone,
    displayEmail,
    displayAccreditation,
    displayNpsn,
    displayPrincipalName,
    phoneDialHref,
    whatsAppUrl,
    isPpdbOpen,
    ppdbPortalUrl,
    siteLogo,
  };
}
