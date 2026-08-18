import { describe, expect, it } from 'vitest';
import { isConsoleShell, isPublicShell, currentAppShell } from '@/config/shell';

describe('app shell (pathname)', () => {
    it('detects console paths', () => {
        Object.defineProperty(window, 'location', {
            value: { pathname: '/dash/platform' },
            writable: true,
        });
        expect(isConsoleShell()).toBe(true);
        expect(isPublicShell()).toBe(false);
        expect(currentAppShell()).toBe('console');
    });

    it('detects public paths', () => {
        Object.defineProperty(window, 'location', {
            value: { pathname: '/member/login' },
            writable: true,
        });
        expect(isConsoleShell()).toBe(false);
        expect(isPublicShell()).toBe(true);
        expect(currentAppShell()).toBe('public');
    });
});
