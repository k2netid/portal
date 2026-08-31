import { computed } from 'vue';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';

export function useSarangengeIdentity() {
  const { getSetting } = useTheme();
  const systemStore = useSystemStore();

  const siteSettings = computed(() => {
    return (systemStore.settings as Record<string, unknown> | undefined) || {};
  });

  const displaySchoolName = computed(() => {
    const fromTheme = getSetting('school_name', '');
    if (fromTheme && typeof fromTheme === 'string') return fromTheme;
    const fromSystem = siteSettings.value.site_name;
    if (fromSystem && typeof fromSystem === 'string') return fromSystem;
    return 'Jejakawan';
  });

  const displayTagline = computed(() => {
    const fromTheme = getSetting('school_tagline', '');
    if (fromTheme && typeof fromTheme === 'string') return fromTheme;
    const fromSystem = siteSettings.value.site_tagline;
    if (fromSystem && typeof fromSystem === 'string') return fromSystem;
    return 'Mekar Bersama Cahaya Pagi — Membentuk Generasi Cerdas & Berkarakter Luhur';
  });

  const displayAddress = computed(() => {
    const fromTheme = getSetting('contact_address', '');
    if (fromTheme && typeof fromTheme === 'string') return fromTheme;
    return 'Jl. Pendidikan No. 45, Bandung, Jawa Barat 40123';
  });

  const displayPhone = computed(() => {
    const fromTheme = getSetting('contact_phone', '');
    if (fromTheme && typeof fromTheme === 'string') return fromTheme;
    return '+62 22 7208899';
  });

  const displayEmail = computed(() => {
    const fromTheme = getSetting('contact_email', '');
    if (fromTheme && typeof fromTheme === 'string') return fromTheme;
    return 'info@sekolahsarangenge.sch.id';
  });

  const displayAccreditation = computed(() => {
    const fromTheme = getSetting('school_accreditation', '');
    if (fromTheme && typeof fromTheme === 'string') return fromTheme;
    return 'Akreditasi A (Unggul)';
  });

  const displayNpsn = computed(() => {
    const fromTheme = getSetting('school_npsn', '');
    if (fromTheme && typeof fromTheme === 'string') return fromTheme;
    return 'NPSN: 20268899';
  });

  const phoneDialHref = computed(() => {
    return `tel:${displayPhone.value.replace(/[^0-9+]/g, '')}`;
  });

  const whatsAppRaw = computed(() => {
    const fromTheme = getSetting('contact_whatsapp', '') || getSetting('whatsapp_hotline', '');
    if (fromTheme && typeof fromTheme === 'string') return fromTheme;
    return '6281234567890';
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

  return {
    displaySchoolName,
    displayTagline,
    displayAddress,
    displayPhone,
    displayEmail,
    displayAccreditation,
    displayNpsn,
    phoneDialHref,
    whatsAppUrl,
    isPpdbOpen,
  };
}
