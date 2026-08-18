import { describe, expect, it, beforeEach } from 'vitest';
import { sanitizeLoginRedirect, isErrorScreenPath } from '@/shared/utils/errorReturn';

describe('useErrorPageNavigation helpers', () => {
    beforeEach(() => {
        sessionStorage.clear();
    });

    it('blocks redirect to error screens and probe paths', () => {
        expect(sanitizeLoginRedirect('/404', '/419')).toBeUndefined();
        expect(sanitizeLoginRedirect('/system', '/419')).toBeUndefined();
        expect(sanitizeLoginRedirect('/about', '/419')).toBe('/about');
    });

    it('recognizes unified error paths', () => {
        expect(isErrorScreenPath('/403')).toBe(true);
        expect(isErrorScreenPath('/maintenance')).toBe(true);
        expect(isErrorScreenPath('/about')).toBe(false);
    });
});
