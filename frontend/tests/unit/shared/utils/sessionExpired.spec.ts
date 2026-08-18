import { describe, expect, it, beforeEach } from 'vitest';
import { buildSessionExpiredHref, buildSessionExpiredQuery } from '@/shared/utils/errorReturn';

describe('session expired navigation', () => {
    beforeEach(() => {
        sessionStorage.clear();
    });

    it('builds /419 with reason and safe redirect', () => {
        const href = buildSessionExpiredHref({
            reason: 'timeout',
            currentPath: '/about',
            redirect: '/about',
        });

        expect(href).toMatch(/^\/419\?/);
        expect(href).toContain('reason=timeout');
        expect(href).toContain('redirect=%2Fabout');
    });

    it('strips redirect to error routes', () => {
        const href = buildSessionExpiredHref({
            reason: 'concurrent',
            currentPath: '/419',
            redirect: '/404',
        });

        expect(href).toContain('reason=concurrent');
        expect(href).not.toContain('redirect=%2F404');
    });

    it('builds router query object', () => {
        const query = buildSessionExpiredQuery({
            reason: 'timeout',
            currentPath: '/about',
        });

        expect(query.reason).toBe('timeout');
        expect(query.redirect).toBe('/about');
    });
});
