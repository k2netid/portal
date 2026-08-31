import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';

export function useLayungIdentity() {
  const { t } = useThemeI18n('layung');
  const { getSetting } = useTheme();
  const systemStore = useSystemStore();

  const displayCompanyName = computed(() => {
    const fromTheme = getSetting('site_title', '') || getSetting('brand_name', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim() !== '') return fromTheme.trim();
    const systemName = (systemStore.settings as { site_name?: string } | undefined)?.site_name;
    if (systemName && typeof systemName === 'string' && systemName.trim() !== '') return systemName.trim();
    return 'Layung Network';
  });

  const displayAsn = computed(() => {
    const custom = getSetting('isp_asn_number', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return 'AS139820 (BGP Multi-Homing Tier-1)';
  });

  const displayTagline = computed(() => {
    const fromTheme = getSetting('site_tagline', '') || getSetting('brand_tagline', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim() !== '') return fromTheme.trim();
    return t('hero.headline', 'Ultra-High Speed Fiber Optic & Managed IT Services');
  });

  const displaySla = computed(() => {
    const custom = getSetting('isp_sla_guarantee', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return '99.999% SLA Uptime';
  });

  const displayNocLatency = computed(() => {
    const custom = getSetting('isp_noc_latency', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return '< 2.4 ms';
  });

  const displayBackboneCapacity = computed(() => {
    const custom = getSetting('isp_backbone_capacity', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return '100 Gbps Redundant Ring';
  });

  const displayAddress = computed(() => {
    const fromContact = getSetting('contact_address', '');
    if (fromContact && typeof fromContact === 'string' && fromContact.trim() !== '') {
      return fromContact.trim();
    }
    const custom = getSetting('isp_address', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return 'Cyber Tower Lt. 18, Jl. Rasuna Said Kav. X-2, Jakarta Selatan, 12950';
  });

  const displayNocPhone = computed(() => {
    const custom = getSetting('isp_noc_phone', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return '+62 21 5088 9988';
  });

  const displaySalesPhone = computed(() => {
    const custom = getSetting('isp_sales_phone', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return '+62 811 9888 2026';
  });

  const displayEmail = computed(() => {
    const custom = getSetting('isp_email', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return 'noc@layung.net.id';
  });

  const nocDialHref = computed(() => `tel:${displayNocPhone.value.replace(/[^0-9+]/g, '')}`);
  const salesDialHref = computed(() => `tel:${displaySalesPhone.value.replace(/[^0-9+]/g, '')}`);

  const nocWhatsAppUrl = computed(() => {
    const custom = getSetting('isp_whatsapp', '');
    const cleanNumber = (custom && typeof custom === 'string' && custom.trim() !== ''
      ? custom
      : displaySalesPhone.value
    ).replace(/[^0-9]/g, '');

    if (!cleanNumber) return '';
    const text = encodeURIComponent(
      'Halo Layung NOC & Sales, saya ingin konsultasi layanan Internet Dedicated / Managed Services untuk perusahaan kami.',
    );
    return `https://wa.me/${cleanNumber}?text=${text}`;
  });

  const coverageCities = computed(() => [
    'Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Medan',
    'Denpasar', 'Makassar', 'Yogyakarta', 'Balikpapan', 'Batam',
  ]);

  return {
    displayCompanyName,
    displayAsn,
    displayTagline,
    displaySla,
    displayNocLatency,
    displayBackboneCapacity,
    displayAddress,
    displayNocPhone,
    displaySalesPhone,
    displayEmail,
    nocDialHref,
    salesDialHref,
    nocWhatsAppUrl,
    coverageCities,
  };
}
