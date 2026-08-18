import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import loggerPlugin, { logger } from '@/shared/utils/logger';

describe('logger util', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.useFakeTimers();
        (logger as any).logCount = 0;
        (logger as any).lastResetTime = Date.now();
        (logger as any).signatureMap.clear();

        vi.spyOn(console, 'error').mockImplementation(() => { });
        vi.spyOn(console, 'warn').mockImplementation(() => { });
        vi.spyOn(console, 'log').mockImplementation(() => { });
        vi.spyOn(console, 'debug').mockImplementation(() => { });
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.restoreAllMocks();
    });

    it('logs info without remote send', () => {
        logger.info('Test info');
        expect(fetch).not.toHaveBeenCalled();
    });

    it('logs error and sends to backend via fetch', async () => {
        logger.error('Test error', { detail: 'error msg', stack: 'stacktrace' });
        await vi.runAllTimersAsync();
        expect(fetch).toHaveBeenCalled();
        const [, init] = vi.mocked(fetch).mock.calls[0] ?? [];
        const body = JSON.parse(String((init as RequestInit)?.body ?? '{}'));
        expect(body.message).toBe('Test error');
        expect(body.level).toBe('error');
    });

    it('includes user_id from localStorage', async () => {
        localStorage.setItem('user', JSON.stringify({ id: 99 }));
        logger.error('Log with user');
        await vi.runAllTimersAsync();
        const [, init] = vi.mocked(fetch).mock.calls[0] ?? [];
        const body = JSON.parse(String((init as RequestInit)?.body ?? '{}'));
        expect(body.user_id).toBe(99);
    });

    it('truncates very long stack traces', async () => {
        const longStack = 'a'.repeat(1500);
        logger.error('Long stack', { stack: longStack });
        await vi.runAllTimersAsync();
        const [, init] = vi.mocked(fetch).mock.calls[0] ?? [];
        const body = JSON.parse(String((init as RequestInit)?.body ?? '{}'));
        expect(body.stack.length).toBeLessThan(1100);
        expect(body.stack).toContain('(truncated for safety)');
    });

    it('deep truncates large objects', async () => {
        const largeObject = { a: 'a'.repeat(300), b: { c: 'c'.repeat(300) }, d: ['d'.repeat(300)] };
        logger.error('Test truncation', largeObject);
        await vi.runAllTimersAsync();
        const [, init] = vi.mocked(fetch).mock.calls[0] ?? [];
        const body = JSON.parse(String((init as RequestInit)?.body ?? '{}'));
        expect(body.data.a).toContain('... (truncated)');
    });

    it('applies rate limiting', async () => {
        for (let i = 0; i < 25; i++) {
            logger.error(`Error ${i}`, { id: i });
        }
        await vi.runAllTimersAsync();
        expect(fetch).toHaveBeenCalledTimes(20);
        expect(console.warn).toHaveBeenCalledWith('[Logger] Rate limit exceeded. Further logs paused for 1 minute.');

        vi.advanceTimersByTime(61000);
        logger.error('Error 26', { id: 26 });
        await vi.runAllTimersAsync();
        expect(fetch).toHaveBeenCalledTimes(21);
    });

    it('handles Vue plugin installation', async () => {
        const app = {
            config: { errorHandler: null, globalProperties: {} },
        };
        loggerPlugin.install(app as any);
        expect(app.config.errorHandler).toBeTypeOf('function');
        expect((app.config.globalProperties as any).$logger).toBe(logger);

        if (app.config.errorHandler) {
            (app.config.errorHandler as any)(new Error('Vue err'), { $options: { __file: 'Test.vue' } } as any, 'info');
        }
        await vi.runAllTimersAsync();
        expect(fetch).toHaveBeenCalled();
    });

    it('deduplicates identical errors within 30s', async () => {
        logger.error('Same error');
        logger.error('Same error');
        await vi.runAllTimersAsync();
        expect(fetch).toHaveBeenCalledTimes(1);

        vi.advanceTimersByTime(31000);
        logger.error('Same error');
        await vi.runAllTimersAsync();
        expect(fetch).toHaveBeenCalledTimes(2);
    });
});
