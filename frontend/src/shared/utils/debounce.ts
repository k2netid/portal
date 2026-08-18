/**
 * Native Debounce Utility
 * 
 * Delays invoking a function until after `delay` milliseconds have elapsed
 * since the last time the debounced function was invoked.
 * 
 * @param fn - The function to debounce
 * @param delay - The number of milliseconds to delay (default: 300)
 * @returns A debounced version of the function
 * 
 * @example
 * const debouncedSearch = debounce(() => fetchResults(), 300)
 * input.addEventListener('input', debouncedSearch)
 */
export function debounce<T extends (...args: unknown[]) => void>(
    fn: T,
    delay: number = 300
): (...args: Parameters<T>) => void {
    let timer: ReturnType<typeof setTimeout> | null = null;

    return (...args: Parameters<T>) => {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => {
            fn(...args);
            timer = null;
        }, delay);
    };
}

/**
 * Throttle Utility
 * 
 * Ensures a function is only called at most once every `limit` milliseconds.
 * Unlike debounce, throttle guarantees regular execution during continuous events.
 * 
 * @param fn - The function to throttle
 * @param limit - The minimum time between calls in milliseconds (default: 300)
 * @returns A throttled version of the function
 * 
 * @example
 * const throttledScroll = throttle(() => updatePosition(), 100)
 * window.addEventListener('scroll', throttledScroll)
 */
export function throttle<T extends (...args: unknown[]) => void>(
    fn: T,
    limit: number = 300
): (...args: Parameters<T>) => void {
    let lastCall = 0;
    let timer: ReturnType<typeof setTimeout> | null = null;

    return (...args: Parameters<T>) => {
        const now = Date.now();
        const remaining = limit - (now - lastCall);

        if (remaining <= 0) {
            if (timer) {
                clearTimeout(timer);
                timer = null;
            }
            lastCall = now;
            fn(...args);
        } else if (!timer) {
            timer = setTimeout(() => {
                lastCall = Date.now();
                timer = null;
                fn(...args);
            }, remaining);
        }
    };
}
