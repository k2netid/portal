import { describe, expect, it, afterEach } from 'vitest';
import { isConsoleShell, isPublicShell, currentAppShell, setAppShell } from '@/config/shell';

describe('app shell', () => {
    afterEach(() => {
        setAppShell('console');
    });

    it('defaults to console shell for the core engine entry', () => {
        expect(isConsoleShell()).toBe(true);
        expect(isPublicShell()).toBe(false);
        expect(currentAppShell()).toBe('console');
    });

    it('can switch to the public theme runtime', () => {
        setAppShell('public');
        expect(isPublicShell()).toBe(true);
        expect(isConsoleShell()).toBe(false);
        expect(currentAppShell()).toBe('public');
    });
});
