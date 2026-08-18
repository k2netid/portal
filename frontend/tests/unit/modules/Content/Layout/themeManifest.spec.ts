import { describe, it, expect } from 'vitest';
import { readJanariCanvasSupport, themeUsesJanariCanvas } from '@/modules/Content/Layout/utils/themeManifest';

describe('themeManifest', () => {
    it('readJanariCanvasSupport reads manifest.supports', () => {
        expect(readJanariCanvasSupport({ supports: { janari_canvas: true } })).toBe(true);
        expect(readJanariCanvasSupport({ supports: { janari_canvas: false } })).toBe(false);
        expect(readJanariCanvasSupport({ supports: {} })).toBe(false);
        expect(readJanariCanvasSupport(null)).toBe(false);
    });

    it('themeUsesJanariCanvas prefers manifest flag over slug', () => {
        expect(
            themeUsesJanariCanvas({
                slug: 'acme-org',
                manifest: { supports: { janari_canvas: true } },
            }),
        ).toBe(true);
    });

    it('themeUsesJanariCanvas falls back to janari slug prefix', () => {
        expect(themeUsesJanariCanvas({ slug: 'janari', manifest: {} })).toBe(true);
        expect(themeUsesJanariCanvas({ slug: 'janari-pro', manifest: {} })).toBe(true);
    });

    it('themeUsesJanariCanvas reads root supports', () => {
        expect(
            themeUsesJanariCanvas({
                slug: 'other',
                supports: { janari_canvas: true },
            }),
        ).toBe(true);
    });
});
