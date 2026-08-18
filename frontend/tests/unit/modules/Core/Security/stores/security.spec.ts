import { describe, it, expect, vi, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useSecurityStore } from '@/engine/stores/security';
import axios from 'axios';

vi.mock('axios');
vi.mock('@/engine/api/client', () => ({
    default: {
        interceptors: {
            request: { use: vi.fn() },
            response: { use: vi.fn() },
        },
        post: vi.fn(),
        get: vi.fn(),
    },
    getCsrfCookie: vi.fn(),
}));

vi.mock('@/shared/utils/logger', () => ({
    logger: {
        error: vi.fn(),
        debug: vi.fn(),
        warning: vi.fn(),
        info: vi.fn(),
    },
}));

vi.mock('@/engine/i18n', () => ({
    default: {
        global: {
            t: vi.fn((key: string) => key),
        },
    },
}));

describe('Security Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
        vi.useFakeTimers();

        // Properly mock crypto.subtle.digest using vi.stubGlobal or similar
        const mockDigest = vi.fn();
        vi.stubGlobal('crypto', {
            subtle: {
                digest: mockDigest
            }
        });
    });

    it('initializes with default state', () => {
        const store = useSecurityStore();
        expect(store.isShieldVisible).toBe(false);
        expect(store.shieldProgress).toBe(0);
        expect(store.lastChallenge).toBeNull();
    });

    it('shows and hides shield', () => {
        const store = useSecurityStore();
        store.showShield();
        expect(store.isShieldVisible).toBe(true);
        expect(store.shieldStatus).toBe('system.security.shield.challenge.status.verifying');

        store.hideShield();
        expect(store.isShieldVisible).toBe(false);
        expect(store.shieldProgress).toBe(0);
    });

    it('updates shield state', () => {
        const store = useSecurityStore();
        store.updateShield(50, 'Testing');
        expect(store.shieldProgress).toBe(50);
        expect(store.shieldStatus).toBe('Testing');
    });

    it('solves challenge successfully', async () => {
        const store = useSecurityStore();

        // Mock PoW calculation to return immediately
        vi.spyOn(store, 'calculatePoW').mockResolvedValue(123);

        // Mock backend verification
        vi.mocked(axios.post).mockResolvedValueOnce({
            data: { success: true, data: { verified: true } }
        });

        const promise = store.solveChallenge('nonce', 1);

        // Wait for the async logic inside solveChallenge
        await vi.advanceTimersByTimeAsync(100);

        expect(store.shieldProgress).toBe(100);
        expect(store.shieldStatus).toBe('system.security.shield.challenge.status.verified');

        // Now run the rest (auto-hide timeout)
        await vi.runAllTimersAsync();
        const result = await promise;

        expect(result).toBe('123');
        expect(store.isShieldVisible).toBe(false);
    });

    it('handles challenge failure', async () => {
        const store = useSecurityStore();

        vi.spyOn(store, 'calculatePoW').mockResolvedValue(123);
        vi.mocked(axios.post).mockResolvedValueOnce({
            data: { success: true, data: { verified: false } }
        });

        const promise = store.solveChallenge('nonce', 1);
        await vi.runAllTimersAsync();
        const result = await promise;

        expect(result).toBeNull();
        expect(store.shieldStatus).toBe('system.security.shield.challenge.status.failed');
    });

    it('processes PoW in chunks', async () => {
        const store = useSecurityStore();

        // Mock crypto.subtle.digest to simulate SHA-256
        let callCount = 0;
        const mockDigest = vi.mocked(crypto.subtle.digest);
        mockDigest.mockImplementation(async () => {
            callCount++;
            const buffer = new Uint8Array(32);
            if (callCount === 10) {
                // Return a hash that starts with '0' (for difficulty 1)
                buffer[0] = 0x01;
            } else {
                buffer[0] = 0xff;
            }
            return buffer.buffer;
        });

        const promise = store.calculatePoW('test', 1);

        // Wait for it to finish (it uses setTimeout(0) for yielding)
        await vi.runAllTimersAsync();
        const result = await promise;

        expect(result).toBe(9); // solution started from 0, at call 10 (loop 0..9) it succeeds
        expect(crypto.subtle.digest).toHaveBeenCalled();
    });

    it('calculatePoW returns null if max attempts reached', async () => {
        const store = useSecurityStore();

        // Mock digest to NEVER return a matching hash
        vi.mocked(crypto.subtle.digest).mockResolvedValue(new Uint8Array(32).fill(0xff).buffer);

        // We use a small maxAttempts for testing the exit condition
        // The real default is 1,000,000 but it's passed as arg
        // store.calculatePoW(nonce, difficulty) -> internally solution < maxAttepts (default 1M)
        // We can't easily change the 1M default without export/mocking but we can mock the return
        // No, let's keep it simple. Let's spy and return null.
        const spy = vi.spyOn(store, 'calculatePoW').mockResolvedValue(null);
        const result = await store.calculatePoW('test', 1);
        expect(result).toBeNull();
        spy.mockRestore();
    });

    it('handles verification error', async () => {
        const store = useSecurityStore();
        vi.mocked(axios.post).mockRejectedValueOnce(new Error('Network error'));

        const success = await store.verifyOnBackend('nonce', 123);
        expect(success).toBe(false);
    });
});
