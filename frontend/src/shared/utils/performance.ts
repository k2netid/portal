/**
 * Performance utilities for throttling and debouncing
 */

/**
 * Creates a debounced function that delays invoking func until after wait milliseconds 
 * have elapsed since the last time the debounced function was invoked.
 */
export function debounce<T extends (...args: unknown[]) => unknown>(func: T, wait: number): (...args: Parameters<T>) => void {
    let timeout: ReturnType<typeof setTimeout> | null = null;

    return function (this: unknown, ...args: Parameters<T>) {
        if (timeout) clearTimeout(timeout);

        timeout = setTimeout(() => {
            (func as (...args: unknown[]) => void).apply(this, args);
        }, wait);
    };
}

/**
 * Creates a throttled function that only invokes func at most once per every wait milliseconds.
 */
export function throttle<T extends (...args: unknown[]) => unknown>(func: T, wait: number): (...args: Parameters<T>) => void {
    let lastTime = 0;

    return function (this: unknown, ...args: Parameters<T>) {
        const now = Date.now();
        if (now - lastTime >= wait) {
            (func as (...args: unknown[]) => void).apply(this, args);
            lastTime = now;
        }
    };
}
