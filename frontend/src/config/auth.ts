/**
 * Authentication Configuration
 * Manages session and security related settings
 */

export interface AuthConfig {
    sessionLifetime: number; // in seconds
}

export const authConfig: AuthConfig = {
    // Session lifetime, defaults to 8 hours if not set
    sessionLifetime: parseInt(import.meta.env.VITE_SESSION_LIFETIME || '28800'),
};

export default authConfig;
