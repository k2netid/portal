import { describe, expect, it } from 'vitest';
import { isUploadedThemeActive } from '@/modules/Content/Layout/utils/dynamicThemeLoader';

describe('dynamicThemeLoader', () => {
  it('detects uploaded theme with bundle url when feature enabled', () => {
    const prev = import.meta.env.VITE_FEATURE_UPLOADED_THEMES;
    import.meta.env.VITE_FEATURE_UPLOADED_THEMES = 'true';
    expect(
      isUploadedThemeActive({ source: 'uploaded', bundle_url: '/storage/themes/x/theme.esm.js' }),
    ).toBe(true);
    import.meta.env.VITE_FEATURE_UPLOADED_THEMES = prev;
  });

  it('rejects bundled themes', () => {
    expect(isUploadedThemeActive({ source: 'bundled', bundle_url: null })).toBe(false);
  });
});
