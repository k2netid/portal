import { describe, expect, it } from 'vitest';
import { resolveIsConsoleEntrypoint } from '@/engine/router/entrypoint';

describe('resolveIsConsoleEntrypoint', () => {
    it('returns true for dashboard route and descendants', () => {
        expect(resolveIsConsoleEntrypoint('/dash')).toBe(true);
        expect(resolveIsConsoleEntrypoint('/dash/users')).toBe(true);
        expect(resolveIsConsoleEntrypoint('/dash/platform')).toBe(true);
    });

    it('returns true for auth/console-related routes', () => {
        expect(resolveIsConsoleEntrypoint('/auth/console-sign-in')).toBe(true);
        expect(resolveIsConsoleEntrypoint('/auth/console-sign-up')).toBe(true);
        expect(resolveIsConsoleEntrypoint('/forgot-password')).toBe(true);
        expect(resolveIsConsoleEntrypoint('/reset-password')).toBe(true);
        expect(resolveIsConsoleEntrypoint('/verify-email')).toBe(true);
    });

    it('returns false for public and error routes', () => {
        expect(resolveIsConsoleEntrypoint('/')).toBe(false);
        expect(resolveIsConsoleEntrypoint('/member/register')).toBe(false);
        expect(resolveIsConsoleEntrypoint('/404')).toBe(false);
        expect(resolveIsConsoleEntrypoint('/about')).toBe(false);
    });
});
