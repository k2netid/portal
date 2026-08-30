import type { HTMLAttributes } from 'vue';

type ClassValue = HTMLAttributes['class'];

/** Local class merge for Janari UI (no dependency on shared/console components). */
export function cn(...parts: ClassValue[]): string {
    return parts
        .flatMap((part) => {
            if (!part) return [];
            if (typeof part === 'string') return [part];
            if (Array.isArray(part)) return part.filter((p): p is string => typeof p === 'string');
            return [];
        })
        .join(' ');
}
