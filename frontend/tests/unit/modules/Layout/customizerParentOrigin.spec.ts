import { describe, expect, it } from 'vitest';
import {
    resolveAllowedCustomizerOrigins,
    isTrustedCustomizerParentOrigin,
    readParentOriginFromQuery,
} from '@/modules/Layout/customizer/preview/protocol';

const SELF = 'https://staging.portal.net';

describe('customizer parent origin allowlist', () => {
    it('ignores ja_parent_origin on the public site', () => {
        const search = '?ja_parent_origin=' + encodeURIComponent('https://evil.example');
        const allowed = resolveAllowedCustomizerOrigins(search, SELF);
        expect(allowed.has(SELF)).toBe(true);
        expect(allowed.has('https://evil.example')).toBe(false);
    });

    it('still ignores a foreign host even inside the preview iframe', () => {
        const search =
            '?ja_customizer_preview=1&ja_parent_origin=' + encodeURIComponent('https://evil.example');
        const allowed = resolveAllowedCustomizerOrigins(search, SELF);
        expect(allowed.has('https://evil.example')).toBe(false);
    });

    it('allows same-hostname parent on a different port in preview', () => {
        const search =
            '?ja_customizer_preview=1&ja_parent_origin=' +
            encodeURIComponent('https://staging.portal.net:5173');
        const allowed = resolveAllowedCustomizerOrigins(search, SELF);
        expect(allowed.has('https://staging.portal.net:5173')).toBe(true);
    });

    it('rejects credentials in the parent origin', () => {
        expect(
            isTrustedCustomizerParentOrigin('https://user:pass@staging.portal.net', SELF),
        ).toBe(false);
    });

    it('rejects non-http parent origins', () => {
        expect(readParentOriginFromQuery('?ja_parent_origin=javascript:alert(1)')).toBeNull();
    });
});
