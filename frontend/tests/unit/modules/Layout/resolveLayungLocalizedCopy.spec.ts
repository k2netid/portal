import { describe, expect, it } from 'vitest';
import {
  DEFAULT_CS_PHONE,
  DEFAULT_NOC_PHONE,
  DEFAULT_SALES_PHONE,
  STALE_HERO_TITLES,
  isInternalAppPath,
  isStalePhone,
  isStaleThemeCopy,
  resolveLayungLocalizedCopy,
  resolveThemeHref,
  toWhatsAppDigits,
} from '@/modules/Layout/views/themes/layung/composables/resolveLayungLocalizedCopy';

describe('resolveLayungLocalizedCopy', () => {
  const settings: Record<string, string> = {
    hero_title: 'Konektivitas Internet & Layanan IT Terkelola untuk Bisnis',
    hero_title_en: '',
    hero_subtitle_id: 'Copy ID dari customizer',
    hero_subtitle_en: 'Copy EN from customizer',
  };

  const getSetting = (key: string) => settings[key] ?? '';

  it('ignores stale unsuffixed Indonesian copy and uses the locale fallback', () => {
    expect(
      resolveLayungLocalizedCopy({
        getSetting,
        locale: 'id',
        key: 'hero_title',
        fallback: 'Internet Service Provider dan Managed Service Provider',
        stale: STALE_HERO_TITLES,
      }),
    ).toBe('Internet Service Provider dan Managed Service Provider');
  });

  it('does not leak Indonesian customizer copy into English', () => {
    settings.hero_title = 'Judul kustom Indonesia';
    expect(
      resolveLayungLocalizedCopy({
        getSetting,
        locale: 'en',
        key: 'hero_title',
        fallback: 'Internet Service Provider and Managed Service Provider',
      }),
    ).toBe('Internet Service Provider and Managed Service Provider');
  });

  it('prefers locale-specific customizer keys', () => {
    expect(
      resolveLayungLocalizedCopy({
        getSetting,
        locale: 'en',
        key: 'hero_subtitle',
        fallback: 'unused',
      }),
    ).toBe('Copy EN from customizer');
  });
});

describe('phone and href helpers', () => {
  it('treats the retired NOC number as stale', () => {
    expect(isStalePhone('+62 811-2236-799')).toBe(true);
    expect(isStalePhone(DEFAULT_CS_PHONE)).toBe(false);
    expect(isStalePhone(DEFAULT_NOC_PHONE)).toBe(false);
    expect(isStalePhone(DEFAULT_SALES_PHONE)).toBe(false);
  });

  it('normalizes local 0-prefix numbers for wa.me', () => {
    expect(toWhatsAppDigits('0851-3629-0851')).toBe('6285136290851');
    expect(toWhatsAppDigits('+62 851-3629-0851')).toBe('6285136290851');
  });

  it('detects in-app paths including hashes', () => {
    expect(isInternalAppPath('/contact#contact-form')).toBe(true);
    expect(isInternalAppPath('https://wa.me/6285136290851')).toBe(false);
    expect(resolveThemeHref(' /services ', '/solusi')).toBe('/services');
  });

  it('treats exact stale strings as empty', () => {
    expect(isStaleThemeCopy('Cek Area Jangkauan', ['Cek Area Jangkauan'])).toBe(true);
    expect(isStaleThemeCopy('Hubungi Kami', ['Cek Area Jangkauan'])).toBe(false);
  });
});
