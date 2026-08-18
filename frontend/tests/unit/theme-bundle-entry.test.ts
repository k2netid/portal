import { describe, expect, it } from 'vitest';
import { resolveComponent } from '@/modules/Content/Layout/views/themes/janari/theme.bundle-entry';

describe('janari theme.bundle-entry', () => {
  it('exports resolveComponent for known pages', { timeout: 15000 }, async () => {
    const home = await resolveComponent('home');
    expect(home).toBeTruthy();
    expect(typeof home === 'object' || typeof home === 'function').toBe(true);
  });

  it('rejects unknown page names', async () => {
    await expect(resolveComponent('not-a-real-page')).rejects.toThrow(/no page/i);
  });
});
