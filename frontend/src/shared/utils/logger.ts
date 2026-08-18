import type { App, ComponentPublicInstance } from 'vue';

interface ErrorLog {
    message: string;
    stack?: string;
    url: string;
    user_agent: string;
    user_id?: number;
    data?: Record<string, unknown>;
    level: 'debug' | 'info' | 'warning' | 'error' | 'critical';
}

const SENSITIVE_KEY_PATTERN = /(token|authorization|cookie|password|passwd|secret|api[_-]?key|signature|nonce)/i;

function toPlainError(err: unknown): Record<string, unknown> | undefined {
    if (!err) return undefined;
    if (err instanceof Error) {
        return {
            name: err.name,
            message: err.message,
            stack: err.stack,
            // Preserve any custom fields on the error instance
            ...(err as any),
        };
    }
    if (typeof err === 'object') {
        try {
            return JSON.parse(JSON.stringify(err)) as Record<string, unknown>;
        } catch {
            return { value: String(err) };
        }
    }
    return { value: String(err) };
}

function toRecord(value: unknown): Record<string, unknown> {
    if (!value) return {};
    if (typeof value === 'object' && !Array.isArray(value)) return value as Record<string, unknown>;
    return { value };
}

class Logger {
    private apiUrl = '/journal/frontend';

    // Rate Limiting & Deduplication Logic
    private logCount = 0;
    private lastResetTime = Date.now();
    private signatureMap = new Map<string, number>();

    constructor() {
        if (import.meta.env.MODE === 'test') {
            return;
        }
        this.setupGlobalHandlers();
    }

    private setupGlobalHandlers() {
        // Window errors
        window.onerror = (message, source, lineno, colno, error) => {
            this.error(message as string, {
                stack: error?.stack,
                source,
                lineno,
                colno
            });
        };

        // Unhandled promise rejections
        window.onunhandledrejection = (event) => {
            this.error(event.reason?.message || 'Unhandled Promise Rejection', {
                stack: event.reason?.stack
            });
        };
    }

    public log(level: ErrorLog['level'], message: string, data: unknown = {}) {
        // Also log to console in development
        if (import.meta.env.DEV) {
            const consoleMethod = level === 'critical' ? 'error' : (level === 'warning' ? 'warn' : (level === 'debug' ? 'debug' : 'log'));

            if (data && typeof data === 'object' && Object.keys(data).length > 0) {
                 
                console[consoleMethod](`[${level.toUpperCase()}] ${message}`, data);
            } else {
                 
                console[consoleMethod](`[${level.toUpperCase()}] ${message}`);
            }
        }

        const sanitizedData = this.redactSensitiveData(toRecord(data)) as Record<string, unknown>;

        const errorLog: ErrorLog = {
            message,
            level,
            url: this.sanitizeUrl(window.location.href),
            user_agent: navigator.userAgent,
            data: sanitizedData,
            stack:
                sanitizedData && typeof sanitizedData === 'object'
                    ? (typeof sanitizedData['stack'] === 'string' ? sanitizedData['stack'] : undefined)
                    : undefined
        };

        // Try to add user ID if available in localStorage or store
        try {
            // Simplified check - adjust based on your auth implementation
            const userStr = localStorage.getItem('user'); // Adjust key as needed
            if (userStr) {
                const user = JSON.parse(userStr);
                errorLog.user_id = user.id;
            }
        } catch {
            // Ignore
        }

        this.send(errorLog);
    }
    public info(message: string, data: unknown = {}) {
        this.log('info', message, data);
    }

    public debug(message: string, data: unknown = {}) {
        this.log('debug', message, data);
    }

    public warning(message: string, data: unknown = {}) {
        this.log('warning', message, data);
    }

    private isProcessingError = false;

    public error(message: string, data: unknown = {}) {
        // Recursion guard: prevent infinite loops if logging itself triggers an error
        if (this.isProcessingError) {
            console.error('[Logger Emergency] Recursion detected while logging:', message);
            return;
        }

        try {
            this.isProcessingError = true;
            this.log('error', message, data);
        } finally {
            this.isProcessingError = false;
        }
    }



    private deepTruncate(obj: unknown, limit = 200, depth = 0): unknown {
        // Prevent infinite recursion
        if (depth > 6) return '[Max Depth Exceeded]';

        if (typeof obj === 'string') {
            return obj.length > limit ? obj.substring(0, limit) + '... (truncated)' : obj;
        }
        if (Array.isArray(obj)) {
            return obj.map(item => this.deepTruncate(item, limit, depth + 1));
        }
        if (obj !== null && typeof obj === 'object') {
            const result: Record<string, unknown> = {};
            for (const key in obj as Record<string, unknown>) {
                // Defensively skip nested stacks or huge payloads
                if (key === 'stack' || key === 'long_stack') continue;
                result[key] = this.deepTruncate((obj as Record<string, unknown>)[key], limit, depth + 1);
            }
            return result;
        }
        return obj;
    }

    private sanitizeUrl(rawUrl: string): string {
        try {
            const currentOrigin = typeof window !== 'undefined' ? window.location.origin : undefined;
            const parsed = new URL(rawUrl, currentOrigin);
            // Keep only non-sensitive query parameters for debugging context.
            const safeParams = new URLSearchParams();
            parsed.searchParams.forEach((value, key) => {
                if (!SENSITIVE_KEY_PATTERN.test(key)) {
                    safeParams.set(key, value);
                }
            });

            const query = safeParams.toString();
            return `${parsed.origin}${parsed.pathname}${query ? `?${query}` : ''}`;
        } catch {
            return rawUrl.split('?')[0] || rawUrl;
        }
    }

    private redactSensitiveData(value: unknown, depth = 0): unknown {
        if (depth > 6) return '[Max Depth Exceeded]';
        if (Array.isArray(value)) {
            return value.map((item) => this.redactSensitiveData(item, depth + 1));
        }
        if (value !== null && typeof value === 'object') {
            const result: Record<string, unknown> = {};
            Object.entries(value as Record<string, unknown>).forEach(([key, val]) => {
                if (SENSITIVE_KEY_PATTERN.test(key)) {
                    result[key] = '[REDACTED]';
                } else {
                    result[key] = this.redactSensitiveData(val, depth + 1);
                }
            });
            return result;
        }
        return value;
    }

    private async send(log: ErrorLog) {
        // Don't send debug, info, or warning logs to backend to prevent flood
        if (['debug', 'info', 'warning'].includes(log.level)) return;

        // Rate Limiting: Reset count every 60 seconds
        const now = Date.now();
        if (now - this.lastResetTime > 60000) {
            this.logCount = 0;
            this.lastResetTime = now;
            // Also clean up signature map older than 60s
            for (const [sig, time] of this.signatureMap.entries()) {
                if (now - time > 60000) this.signatureMap.delete(sig);
            }
        }

        // Limit to 20 logs per minute
        if (this.logCount >= 20) {
            if (this.logCount === 20) {
                console.warn('[Logger] Rate limit exceeded. Further logs paused for 1 minute.');
                this.logCount++;
            }
            return;
        }

        // Aggressive Truncation: Limit stack trace to 1KB
        if (log.stack && log.stack.length > 1000) {
            log.stack = log.stack.substring(0, 1000) + '\n... (truncated for safety)';
        }

        // Deep Truncation of data object
        if (log.data) {
            log.data = this.deepTruncate(log.data) as Record<string, unknown>;
        }

        // Deduplication: Use signature cache with 30s TTL
        const signature = `${log.message}|${log.stack?.substring(0, 150) || ''}`;
        const lastSeen = this.signatureMap.get(signature);
        if (lastSeen && now - lastSeen < 30000) {
            return;
        }
        this.signatureMap.set(signature, now);

        this.logCount++;

        try {
            // Stability: avoid depending on axios/bootstrap during early boot.
            // Prefer sendBeacon when possible (survives page unload), fallback to fetch.
            const url = '/api/v1' + this.apiUrl;
            const payload = JSON.stringify(log);

            const canBeacon =
                typeof navigator !== 'undefined' &&
                typeof navigator.sendBeacon === 'function' &&
                // sendBeacon expects a smallish payload; keep our existing truncation anyway
                payload.length < 60_000;

            if (canBeacon) {
                const ok = navigator.sendBeacon(
                    url,
                    new Blob([payload], { type: 'application/json' })
                );
                if (ok) return;
            }

            await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload,
                // keepalive helps when navigating away quickly
                keepalive: true,
                credentials: 'same-origin'
            });
        } catch (e) {
            console.error('Failed to send log to backend', e);
        }
    }
}

export const logger = new Logger();

// Vue Plugin
export default {
    install(app: App) {
        app.config.errorHandler = (err: unknown, instance: ComponentPublicInstance | null, info: string) => {
            const isError = err instanceof Error;
            const message = isError ? (err.message || 'Vue Error') : (typeof err === 'string' ? err : 'Vue Error');

            // Vue 3: `$options` is not always populated the same way in production builds.
            const component =
                (instance as any)?.$?.type?.__file ||
                (instance as any)?.$?.type?.name ||
                (instance as any)?.$options?.__file ||
                (instance as any)?.$options?.name;

            logger.error(message, {
                stack: isError ? err.stack : undefined,
                name: isError ? err.name : undefined,
                component,
                info,
                // Preserve raw value for debugging (will be truncated before sending)
                raw: toPlainError(err)
            });
            console.error(err);
        };

        // Make $logger available globally
        app.config.globalProperties.$logger = logger;
    }
};
