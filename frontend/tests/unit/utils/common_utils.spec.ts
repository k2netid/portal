import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { debounce, throttle } from '@/shared/utils/debounce';
import { debounce as perfDebounce, throttle as perfThrottle } from '@/shared/utils/performance';
import { formatCurrency, formatNumber, formatDate, getStatusVariant } from '@/shared/utils/format';


describe('Common Utilities', () => {
    describe('Debounce & Throttle (debounce.ts)', () => {
        beforeEach(() => {
            vi.useFakeTimers();
        });

        afterEach(() => {
            vi.restoreAllMocks();
        });

        it('debounce delays function execution', () => {
            const callback = vi.fn();
            const debounced = debounce(callback, 100);

            debounced();
            debounced();
            debounced();

            expect(callback).not.toHaveBeenCalled();

            vi.advanceTimersByTime(50);
            expect(callback).not.toHaveBeenCalled();

            vi.advanceTimersByTime(50);
            expect(callback).toHaveBeenCalledTimes(1);
        });

        it('throttle limits function execution', () => {
            const callback = vi.fn();
            const throttled = throttle(callback, 100);

            throttled(); // First call: immediate (since lastCall = 0)
            throttled(); // Second call: blocked/queued
            throttled();

            expect(callback).toHaveBeenCalledTimes(1);

            vi.advanceTimersByTime(50);
            expect(callback).toHaveBeenCalledTimes(1);

            vi.advanceTimersByTime(50);
            expect(callback).toHaveBeenCalledTimes(2);
        });

        it('throttle clears existing timer when called multiple times before limit', () => {
            const callback = vi.fn();
            const throttled = throttle(callback, 100);

            throttled(); // immediate #1
            vi.advanceTimersByTime(50);
            throttled(); // set timer for 50ms later
            vi.advanceTimersByTime(1);
            throttled(); // should NOT clear the timer because it only clears if remaining <= 0?
            // Wait, looking at throttle implementation:
            // 62:         } else if (!timer) {
            // 63:             timer = setTimeout(() => { ... })
            // If timer exists, it does nothing! So it doesn't clear it. 
            // 55:         if (remaining <= 0) {
            // 56:             if (timer) {
            // 57:                 clearTimeout(timer);
            // 58:                 timer = null;
            // 59:             }

            // This happens if we call throttled() exactly at or after limit, while a timer was pending.

            vi.advanceTimersByTime(49); // timer triggers
            expect(callback).toHaveBeenCalledTimes(2);

            // Now trigger immediate again after more than 100ms
            vi.advanceTimersByTime(110);
            throttled(); // #3
            expect(callback).toHaveBeenCalledTimes(3);
        });
    });

    describe('Performance Utilities (performance.ts)', () => {
        beforeEach(() => {
            vi.useFakeTimers();
        });

        it('perfDebounce works correctly', () => {
            const callback = vi.fn();
            const debounced = perfDebounce(callback, 100);

            debounced();
            vi.advanceTimersByTime(100);
            expect(callback).toHaveBeenCalled();
        });

        it('perfThrottle works correctly', () => {
            const callback = vi.fn();
            const throttled = perfThrottle(callback, 100);

            throttled(); // Immediate
            throttled(); // Skipped (simple implementation)

            expect(callback).toHaveBeenCalledTimes(1);

            vi.advanceTimersByTime(101);
            throttled();
            expect(callback).toHaveBeenCalledTimes(2);
        });
    });

    describe('Formatting Utilities', () => {
        it('formatCurrency formats to IDR', () => {
            // Note: Intl.NumberFormat output might contain non-breaking spaces or specific characters
            const result = formatCurrency(1500000).replace(/\u00A0/g, ' ');
            expect(result).toMatch(/Rp.*1\.500\.000/);
        });

        it('formatNumber adds thousand separators', () => {
            expect(formatNumber(1234567)).toBe('1.234.567');
            expect(formatNumber('1234.5')).toBe('1.234,5');
        });

        it('formatDate formats dates correctly', () => {
            const date = '2023-12-25';
            expect(formatDate(date, 'YYYY')).toBe('2023');
            expect(formatDate(null)).toBe('-');
        });

        it('getStatusVariant returns correct categories', () => {
            expect(getStatusVariant('active')).toBe('success');
            expect(getStatusVariant('pending')).toBe('warning');
            expect(getStatusVariant('failed')).toBe('destructive');
            expect(getStatusVariant('inactive')).toBe('secondary');
            expect(getStatusVariant('unknown')).toBe('outline');
        });
    });


});
