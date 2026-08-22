import axios, { 
    type AxiosInstance, 
    type AxiosResponse, 
    type AxiosError, 
    type InternalAxiosRequestConfig,
    type AxiosRequestConfig,
    isAxiosError
} from 'axios';

export interface ApiRequestConfig extends AxiosRequestConfig {
    _skipManualRedirect?: boolean;
}
import { logger } from '@/shared/utils/logger';
import { getActivePinia } from 'pinia';
import { buildSessionExpiredHref } from '@/shared/utils/errorReturn';

// --- SECURITY & STATE FLAGS ---
let isRedirectingToLogin = false;
let abortController = new AbortController();

declare global {
    interface Window {
        __isSessionTerminated?: boolean;
        __factoryResetInProgress?: boolean;
    }
}

window.__isSessionTerminated = false;

// --- PERFORMANCE MONITORING ---
interface ApiPerfEntry {
    url: string;
    method: string;
    status: number;
    durationMs: number;
    at: number;
}

const apiPerfBuffer: ApiPerfEntry[] = [];
const MAX_API_PERF_BUFFER = 250;

const pushApiPerfEntry = (entry: ApiPerfEntry): void => {
    apiPerfBuffer.push(entry);
    if (apiPerfBuffer.length > MAX_API_PERF_BUFFER) {
        apiPerfBuffer.splice(0, apiPerfBuffer.length - MAX_API_PERF_BUFFER);
    }
};

export const consumeApiPerfEntries = (): ApiPerfEntry[] => {
    if (apiPerfBuffer.length === 0) return [];
    const out = [...apiPerfBuffer];
    apiPerfBuffer.length = 0;
    return out;
};

// --- API CLIENT CONFIG ---
const apiClient: AxiosInstance = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true,
    xsrfCookieName: 'XSRF-TOKEN',
    xsrfHeaderName: 'X-XSRF-TOKEN',
});

/**
 * Ensure CSRF cookie is set (call this before first authenticated request)
 */
export const hasStatefulSessionCookie = (): boolean => {
    if (typeof document === 'undefined') {
        return false;
    }
    // Session cookie is HttpOnly; XSRF-TOKEN indicates an active Laravel session.
    return document.cookie.includes('XSRF-TOKEN=');
};

export const getCsrfCookie = async (): Promise<boolean> => {
    try {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
        return true;
    } catch (error) {
        const status = isAxiosError(error) ? error.response?.status : undefined;
        // 401 after session regenerate is expected during login handoff — not a hard failure.
        if (status !== 401) {
            logger.error('Failed to get CSRF cookie:', error);
        }
        return false;
    }
};

// --- UTILITIES ---
export const triggerVaporLock = (): void => {
    if (window.__isSessionTerminated) return;
    window.__isSessionTerminated = true;
    abortController.abort('Vapor Lock: Session Terminated');
    logger.warning('[Security] Vapor Lock triggered. All requests cancelled.');
};

export const resetLockdown = (): void => {
    window.__isSessionTerminated = false;
    isRedirectingToLogin = false;
    abortController = new AbortController();
    logger.info('[Security] Lockdown reset.');
};

// --- INTERCEPTORS ---

// Request: Auth scoping & Vapor Lock
apiClient.interceptors.request.use((config: InternalAxiosRequestConfig & { _perfStartedAt?: number }) => {
    config._perfStartedAt = performance.now();
    const authEndpoints = ['login', 'register', 'forgot-password', 'reset-password', 'sanctum/csrf-cookie', 'logout', 'user'];
    const isAuthRequest = authEndpoints.some(endpoint => config.url?.includes(endpoint));

    // Vapor Lock check
    if (window.__isSessionTerminated && !isAuthRequest) {
        return Promise.reject(new axios.Cancel('Vapor Lock: Request Blocked'));
    }

    if (!isAuthRequest && !config.signal) {
        config.signal = abortController.signal;
    }

    return config;
});

// Response: Global Error Handling & Data Unpacking
apiClient.interceptors.response.use(
    (response: AxiosResponse) => {
        const config = response.config as InternalAxiosRequestConfig & { _perfStartedAt?: number };
        const startedAt = config._perfStartedAt || performance.now();
        
        pushApiPerfEntry({
            url: config.url || 'unknown',
            method: (config.method || 'get').toUpperCase(),
            status: response.status,
            durationMs: Math.max(0, performance.now() - startedAt),
            at: Date.now(),
        });

        const contentType = String(response.headers['content-type'] ?? '');
        if (contentType.includes('text/html')) {
            return Promise.reject(
                new axios.AxiosError(
                    'API returned HTML instead of JSON',
                    'ERR_BAD_RESPONSE',
                    response.config,
                    response.request,
                    response,
                ),
            );
        }

        // Standardized Data Unpacking
        const responseData = response.data;
        if (responseData && typeof responseData === 'object' && 'success' in responseData) {
            if (responseData.success && responseData.data !== undefined) {
                if ('meta' in responseData) {
                    (response as any).meta = responseData.meta;
                }
                response.data = responseData.data;
            }
        }
        return response;
    },
    async (error: AxiosError) => {
        const config = error.config as (InternalAxiosRequestConfig & { _perfStartedAt?: number; _skipManualRedirect?: boolean }) | undefined;
        if (config) {
            const startedAt = config._perfStartedAt || performance.now();
            pushApiPerfEntry({
                url: config.url || 'unknown',
                method: (config.method || 'get').toUpperCase(),
                status: error.response?.status ?? 0,
                durationMs: Math.max(0, performance.now() - startedAt),
                at: Date.now(),
            });
        }
        const status = error.response?.status;
        const currentPath = window.location.pathname;

        // Permission denied — do not treat as session expiry
        if (status === 403) {
            return Promise.reject(error);
        }

        // 1. Session Expiry (401/419)
        if (status === 401 || status === 419) {
            if (window.__factoryResetInProgress) {
                return Promise.reject(error);
            }

            if (isRedirectingToLogin) return Promise.reject(error);
            
            // Respect manual skip flag from request config
            if (config?._skipManualRedirect) {
                return Promise.reject(error);
            }
            
            const url = error.config?.url || '';
            const responseMessage = String(
                (error.response?.data as { message?: string })?.message ?? '',
            ).toLowerCase();
            const isCsrfMismatch = status === 419 && (
                responseMessage.includes('csrf')
                || responseMessage.includes('token mismatch')
            );

            // Skip redirection for logout (intended), public endpoints, or user profile fetch (can be guest)
            if (
                url.includes('logout')
                || url.includes('auth/me')
                || url.includes('user')
                || url.includes('/public/')
                || url.includes('factory-reset')
                || url.includes('maintenance')
            ) {
                return Promise.reject(error);
            }

            // DO NOT redirect if we are already on a public/auth page that handles it gracefully
            if (
                currentPath.includes('/login')
                || currentPath.includes('/auth/')
                || currentPath.includes('/setup')
                || currentPath.includes('/install')
                || currentPath.includes('/419')
            ) {
                return Promise.reject(error);
            }

            // CSRF desync after session regenerate: refresh cookie once instead of session-expired UX
            if (isCsrfMismatch) {
                try {
                    await getCsrfCookie();
                    if (config) {
                        return apiClient(config);
                    }
                } catch {
                    return Promise.reject(error);
                }
                return Promise.reject(error);
            }

            triggerVaporLock();
            isRedirectingToLogin = true;

            logger.error(`[Auth] Session expired (${status}). Redirecting to login.`);

            // Clear Pinia + localStorage — prevents login ↔ dashboard redirect loop
            try {
                const pinia = getActivePinia();
                if (pinia) {
                    const { useAuthStore } = await import('@/modules/Core/System/stores/auth');
                    useAuthStore(pinia).clearAuth();
                }
            } catch {
                localStorage.removeItem('user');
            }

            window.location.assign(buildSessionExpiredHref({
                reason: 'timeout',
                currentPath,
            }));
            return new Promise(() => {}); // Stop execution
        }

        // 2. Shield Challenge (429 Rate Limit)
        if (status === 429) {
            const shieldNonce = error.response?.headers?.['x-shield-challenge'];
            if (shieldNonce) {
                logger.info('[Security] Bot shield challenge detected. Handling...');
                // Logic for solving challenge would go here (importing security store)
            }
        }

        // 3. Maintenance (503)
        if (status === 503) {
            window.location.href = '/maintenance';
        }

        return Promise.reject(error);
    }
);

export default apiClient;
