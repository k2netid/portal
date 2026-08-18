/**
 * API Configuration
 * Manages endpoints and external service URLs
 */

export interface ApiConfig {
    baseUrl: string;
    externalUrl: string;
    timeout: number;
}

export const apiConfig: ApiConfig = {
    // Local proxy or relative path for Sanctum/Laravel
    baseUrl: '/api/v1',
    
    // External URL used for file exports, public forms, or absolute links
    externalUrl: import.meta.env.VITE_API_URL || '',
    
    // Default timeout for axios requests (30s)
    timeout: 30000,
};

export default apiConfig;
