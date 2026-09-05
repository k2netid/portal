const STALE_PHONES = [
  '+62 811-2236-799',
  '628112236799',
  '+628112236799',
  '08112236799',
];

export const STALE_HERO_BADGES = [
  'ISP & Managed Service Provider',
  'Infrastruktur Serat Optik & Managed Services 2026',
];

export const STALE_HERO_TITLES = [
  'Konektivitas Internet & Layanan IT Terkelola untuk Bisnis',
  'Internet Connectivity & Managed IT for Business',
  'Konektivitas Fiber Ultra Cepat & Solusi IT Terkelola untuk Bisnis Skala Global',
  'Konektivitas Fiber Gancang Pisan & Solusi IT Terkelola pikeun Bisnis Skala Global',
];

export const STALE_HERO_SUBTITLES = [
  'Internet dedicated, fiber, dan managed services dari NOC kami di Bandung.',
  'Dedicated internet, fiber, and managed services from our NOC in Bandung.',
  'Hadirkeun backbone internét gancang siga cahaya, laténsi super handap sahandapeun 3ms, multi-cloud interconnect, sarta panyalindungan Cyber SOC 24/7.',
];

export const STALE_HERO_CTAS = [
  'Cek Area Jangkauan',
  'Check Coverage Area',
  'Pariksa Wewengkon Jangkauan',
  'Konsultasi Enterprise',
  'Enterprise Consultation',
  'Konsultasi Énterprise',
];

export const DEFAULT_CS_PHONE = '+62 851-3629-0851';
export const DEFAULT_NOC_PHONE = '+62 851-3629-0861';
export const DEFAULT_SALES_PHONE = '+62 851-3629-0871';
export const DEFAULT_SERVICE_DESK_PHONE = '+62 851-3629-0852';
export const DEFAULT_WHATSAPP_DIGITS = '6285136290851';

export const DEFAULT_INFO_EMAIL = 'info@portal.net';
export const DEFAULT_CS_EMAIL = 'cs@portal.net';
export const DEFAULT_SALES_EMAIL = 'sales@portal.net';
export const DEFAULT_BILLING_EMAIL = 'billing@portal.net';

export function isStaleThemeCopy(value: unknown, stale: readonly string[] = []): boolean {
  if (value == null) return true;
  const text = String(value).trim();
  if (!text) return true;
  return stale.some((item) => item.trim() === text);
}

export function isStalePhone(value: unknown): boolean {
  if (value == null) return true;
  const digits = String(value).replace(/[^0-9]/g, '');
  if (!digits) return true;
  return STALE_PHONES.some((item) => item.replace(/[^0-9]/g, '') === digits);
}

/**
 * Locale-specific customizer keys first. Unsuffixed `key` is Indonesian only,
 * so English/Sundanese never inherit Indonesian copy (the previous mix bug).
 */
export function resolveLayungLocalizedCopy(opts: {
  getSetting: (key: string, fallback?: unknown) => unknown;
  locale: string;
  key: string;
  fallback: string;
  stale?: readonly string[];
}): string {
  const lang = localeBase(opts.locale);
  const stale = opts.stale ?? [];
  const pick = (raw: unknown): string => {
    if (isStaleThemeCopy(raw, stale)) return '';
    return String(raw).trim();
  };

  const fromLocaleKey = pick(opts.getSetting(`${opts.key}_${lang}`));
  if (fromLocaleKey) return fromLocaleKey;

  if (lang === 'id') {
    const base = pick(opts.getSetting(opts.key));
    if (base) return base;
  }

  return opts.fallback;
}

export function resolveThemeHref(raw: unknown, fallback: string): string {
  if (typeof raw === 'string' && raw.trim()) return raw.trim();
  return fallback;
}

export function isInternalAppPath(href: string): boolean {
  return href.startsWith('/') && !href.startsWith('//');
}

export function toWhatsAppDigits(raw: string): string {
  let digits = raw.replace(/[^0-9]/g, '');
  if (digits.startsWith('0')) digits = `62${digits.slice(1)}`;
  return digits;
}

function localeBase(code: string): string {
  const trimmed = (code || '').trim().toLowerCase();
  const base = trimmed.split('-')[0] || 'id';
  if (base === 'in') return 'id';
  return base || 'id';
}
