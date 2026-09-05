import { computed } from 'vue';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { useTheme } from '@/modules/Layout/composables/useTheme';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import {
  DEFAULT_BILLING_EMAIL,
  DEFAULT_CS_EMAIL,
  DEFAULT_CS_PHONE,
  DEFAULT_INFO_EMAIL,
  DEFAULT_NOC_PHONE,
  DEFAULT_SALES_EMAIL,
  DEFAULT_SALES_PHONE,
  DEFAULT_SERVICE_DESK_PHONE,
  DEFAULT_WHATSAPP_DIGITS,
  isStalePhone,
  toWhatsAppDigits,
} from '@/modules/Layout/views/themes/layung/composables/resolveLayungLocalizedCopy';
import {
  LAYUNG_DEFAULT_OFFICE_BANDUNG,
  LAYUNG_DEFAULT_OFFICE_GARUT,
  LAYUNG_DEFAULT_STORE,
} from '@/modules/Layout/views/themes/layung/composables/layungAddresses';

function toTelHref(phone: string): string {
  return `tel:${phone.replace(/[^0-9+]/g, '')}`;
}

function resolvePhoneSetting(
  getSetting: (key: string, defaultValue?: unknown) => unknown,
  key: string,
  fallback: string,
): string {
  const custom = getSetting(key, '');
  if (typeof custom === 'string' && custom.trim() && !isStalePhone(custom)) return custom.trim();
  return fallback;
}

function resolveEmailSetting(
  getSetting: (key: string, defaultValue?: unknown) => unknown,
  key: string,
  fallback: string,
): string {
  const custom = getSetting(key, '');
  if (typeof custom === 'string' && custom.trim()) return custom.trim();
  return fallback;
}

export function useLayungIdentity() {
  const { t } = useThemeI18n('layung');
  const { getSetting } = useTheme();
  const systemStore = useSystemStore();

  const displayCompanyName = computed(() => {
    const fromTheme = getSetting('site_title', '') || getSetting('brand_name', '') || getSetting('site_name', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim() !== '') return fromTheme.trim();
    const systemName = (systemStore.settings as { site_name?: string } | undefined)?.site_name || systemStore.siteSettings?.site_name;
    if (systemName && typeof systemName === 'string' && systemName.trim() !== '') return systemName.trim();
    return 'Portal Layanan';
  });

  const displayLegalName = computed(() => {
    const custom = getSetting('isp_legal_name', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return 'PT Kirana Karina Network';
  });

  const displayAsn = computed(() => {
    const custom = getSetting('isp_asn_number', '');
    const raw = custom && typeof custom === 'string' && custom.trim() !== '' ? custom.trim() : 'AS153992';
    const match = raw.match(/AS\d+/i);
    return match ? match[0].toUpperCase() : raw;
  });

  const displayAsName = computed(() => {
    const custom = getSetting('isp_as_name', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return 'IDNIC-K2NET-ID';
  });

  const displayPrefix = computed(() => {
    const custom = getSetting('isp_ipv4_prefix', '') || getSetting('isp_backbone_capacity', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return '165.99.252.0/24';
  });

  const displayTagline = computed(() => {
    const fromTheme = getSetting('site_tagline', '') || getSetting('brand_tagline', '');
    if (fromTheme && typeof fromTheme === 'string' && fromTheme.trim() !== '') return fromTheme.trim();
    const systemTagline = (systemStore.siteSettings as { site_tagline?: string; site_description?: string } | undefined)?.site_tagline
      || (systemStore.siteSettings as { site_tagline?: string; site_description?: string } | undefined)?.site_description
      || (systemStore.settings as { site_tagline?: string; site_description?: string } | undefined)?.site_tagline
      || (systemStore.settings as { site_tagline?: string; site_description?: string } | undefined)?.site_description;
    if (systemTagline && typeof systemTagline === 'string' && systemTagline.trim() !== '') return systemTagline.trim();
    return t('hero.headline', 'Internet Service Provider & Managed IT Services');
  });

  const displaySla = computed(() => {
    const custom = getSetting('isp_sla_guarantee', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return 'SLA berbasis kontrak';
  });

  const displayNocLatency = computed(() => {
    const custom = getSetting('isp_noc_latency', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return 'Regional Jawa Barat';
  });

  const displayBackboneCapacity = computed(() => {
    const custom = getSetting('isp_backbone_capacity', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return '165.99.252.0/24';
  });

  const displayAddress = computed(() => {
    const fromContact = getSetting('contact_address', '');
    if (fromContact && typeof fromContact === 'string' && fromContact.trim() !== '') {
      return fromContact.trim();
    }
    const custom = getSetting('isp_address', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return LAYUNG_DEFAULT_OFFICE_BANDUNG;
  });

  const displayStoreAddress = computed(() => {
    const custom = getSetting('isp_store_address', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return LAYUNG_DEFAULT_STORE;
  });

  const displayGarutAddress = computed(() => {
    const custom = getSetting('isp_garut_address', '');
    if (custom && typeof custom === 'string' && custom.trim() !== '') return custom.trim();
    return LAYUNG_DEFAULT_OFFICE_GARUT;
  });

  const displayCsPhone = computed(() => resolvePhoneSetting(getSetting, 'isp_cs_phone', DEFAULT_CS_PHONE));
  const displayNocPhone = computed(() => resolvePhoneSetting(getSetting, 'isp_noc_phone', DEFAULT_NOC_PHONE));
  const displaySalesPhone = computed(() => resolvePhoneSetting(getSetting, 'isp_sales_phone', DEFAULT_SALES_PHONE));
  const displayServiceDeskPhone = computed(() =>
    resolvePhoneSetting(getSetting, 'isp_service_desk_phone', DEFAULT_SERVICE_DESK_PHONE),
  );

  const displayEmail = computed(() => resolveEmailSetting(getSetting, 'isp_email', DEFAULT_INFO_EMAIL));
  const displayCsEmail = computed(() => resolveEmailSetting(getSetting, 'isp_cs_email', DEFAULT_CS_EMAIL));
  const displaySalesEmail = computed(() => resolveEmailSetting(getSetting, 'isp_sales_email', DEFAULT_SALES_EMAIL));
  const displayBillingEmail = computed(() => resolveEmailSetting(getSetting, 'isp_billing_email', DEFAULT_BILLING_EMAIL));

  const csDialHref = computed(() => toTelHref(displayCsPhone.value));
  const nocDialHref = computed(() => toTelHref(displayNocPhone.value));
  const salesDialHref = computed(() => toTelHref(displaySalesPhone.value));
  const serviceDeskDialHref = computed(() => toTelHref(displayServiceDeskPhone.value));

  const whatsAppChatUrl = (phone: string): string => {
    const digits = toWhatsAppDigits(phone);
    return digits ? `https://wa.me/${digits}` : '';
  };

  const csWhatsAppUrl = computed(() => whatsAppChatUrl(displayCsPhone.value));
  const nocLineWhatsAppUrl = computed(() => whatsAppChatUrl(displayNocPhone.value));
  const salesWhatsAppUrl = computed(() => whatsAppChatUrl(displaySalesPhone.value));
  const serviceDeskWhatsAppUrl = computed(() => whatsAppChatUrl(displayServiceDeskPhone.value));

  const nocWhatsAppUrl = computed(() => {
    const custom = getSetting('isp_whatsapp', '');
    const source = typeof custom === 'string' && custom.trim() && !isStalePhone(custom)
      ? custom
      : displayCsPhone.value;
    const cleanNumber = toWhatsAppDigits(source) || DEFAULT_WHATSAPP_DIGITS;
    if (!cleanNumber) return '';
    const text = encodeURIComponent(
      t(
        'hero.whatsappPrefill',
        'Halo K2NET, saya ingin konsultasi layanan internet dan IT terkelola untuk perusahaan kami.',
      ),
    );
    return `https://wa.me/${cleanNumber}?text=${text}`;
  });

  const displayBrandLogo = computed(() => {
    const custom = getSetting('brand_logo', '');
    if (typeof custom === 'string' && custom.trim()) {
      const value = custom.trim();
      const isGenericEngineLogo = value === '/logo.png' || value.endsWith('/logo.png');
      if (!isGenericEngineLogo) return value;
    }
    return '/logofull_k2net.png';
  });

  return {
    displayCompanyName,
    displayLegalName,
    displayBrandLogo,
    displayAsn,
    displayAsName,
    displayPrefix,
    displayTagline,
    displaySla,
    displayNocLatency,
    displayBackboneCapacity,
    displayAddress,
    displayStoreAddress,
    displayGarutAddress,
    displayCsPhone,
    displayNocPhone,
    displaySalesPhone,
    displayServiceDeskPhone,
    displayEmail,
    displayCsEmail,
    displaySalesEmail,
    displayBillingEmail,
    csDialHref,
    nocDialHref,
    salesDialHref,
    serviceDeskDialHref,
    csWhatsAppUrl,
    nocLineWhatsAppUrl,
    salesWhatsAppUrl,
    serviceDeskWhatsAppUrl,
    nocWhatsAppUrl,
  };
}
