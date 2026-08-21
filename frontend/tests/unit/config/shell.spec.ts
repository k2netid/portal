import { describe, expect, it } from 'vitest';
import { isConsoleShell, isPublicShell, currentAppShell } from '@/config/shell';

describe('app shell (pathname)', () => {
    it('always operates in console shell for core engine', () => {
        expect(isConsoleShell()).toBe(true);
        expect(isPublicShell()).toBe(false);
        expect(currentAppShell()).toBe('console');
    });
});
