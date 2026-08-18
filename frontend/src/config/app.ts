/**
 * App Configuration
 * Centered app metadata and environment flags
 */

export interface AppConfig {
    name: string;
    domain: string;
    url: string;
    version: string;
    isDev: boolean;
}

const getDefaultUrl = () => {
    // 1. Priority: .env variable
    if (import.meta.env.VITE_CONSOLE_URL) return import.meta.env.VITE_CONSOLE_URL;
    
    // 2. Fallback: Browser detection (Runtime)
    if (typeof window !== 'undefined') {
        const protocol = window.location.protocol;
        const host = import.meta.env.VITE_ROOT_DOMAIN || window.location.hostname;
        return `${protocol}//${host}`;
    }
    
    // 3. Last Resort: Hardcoded local fallback
    return `http://localhost`;
};

export const appConfig: AppConfig = {
    name: import.meta.env.VITE_APP_NAME || 'Jejakawan',
    domain: import.meta.env.VITE_ROOT_DOMAIN || (typeof window !== 'undefined' ? window.location.hostname : 'localhost'),
    url: getDefaultUrl(),
    version: '2.0.0-pro',
    isDev: import.meta.env.DEV,
};

export default appConfig;
