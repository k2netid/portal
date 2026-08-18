/**
 * Standardized Hybrid Configuration for Jejakawan
 * Centrally manages environment variables with type safety and defaults.
 */

import { appConfig, type AppConfig } from './app';
import { apiConfig, type ApiConfig } from './api';
import { authConfig, type AuthConfig } from './auth';

export interface RootConfig {
    app: AppConfig;
    api: ApiConfig;
    auth: AuthConfig;
    
    // Flattened aliases for older imports
    appDomain: string;
    appUrl: string;
    apiBaseUrl: string;
    storageBaseUrl: string;
}

export const config: RootConfig = {
    app: appConfig,
    api: apiConfig,
    auth: authConfig,
    
    // Top-level aliases (prefer config.app / config.api in new code)
    appDomain: appConfig.domain,
    appUrl: appConfig.url,
    apiBaseUrl: apiConfig.baseUrl,
    storageBaseUrl: '/storage',
};

export default config;
export { appConfig, apiConfig, authConfig };
