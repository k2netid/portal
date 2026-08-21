import { logger } from '@/shared/utils/logger';
import { appConfig } from '@/config';
import { SECURITY_ROUTES } from '@/config/security';
import { defineStore } from 'pinia';
import type { AxiosResponse } from 'axios';
import { isCancel, isAxiosError } from 'axios';
import api, { getCsrfCookie, resetLockdown, type ApiRequestConfig } from '@/engine/api/client';
import type { User, Role, AuthState, AuthResponse, LoginCredentials, RegisterData, ResetPasswordData } from '@/engine/types/auth';
import { userModelSchema, authResponseSchema } from '@/shared/schemas';

interface ApiErrorResponse {
    message?: string;
    errors?: Record<string, string[]>;
    retry_after?: string;
    requires_verification?: boolean;
    user_id?: string;
    requires_two_factor?: boolean;
}

/** Hub RBAC: internal operators vs subscription Jejakawan. */
export const ROLE_RANKS: Record<string, number> = {
    super: 100,
    'system-admin': 90,
    'security-officer': 85,
    editor: 60,
    staff: 50,
    member: 10,
    // Legacy names (tests / old data)
    admin: 95,
    operator: 85,
};

// Define extended window interface for circuit breaker flags
declare global {
    interface Window {
        __isSessionTerminated?: boolean;
        __is403Blocked?: boolean;
    }
}


export const useAuthStore = defineStore('auth', {
    state: (): AuthState & { initialized: boolean; authBootstrapComplete: boolean } => ({
        user: null,
        isAuthenticated: false,
        initialized: false,
    authBootstrapComplete: false,
    }),

    getters: {
        // Helper to calculate role rank from a user object
        getRoleRank: (state) => (user: User | null = null): number => {
            const targetUser = user || state.user;
            if (!targetUser || !targetUser.roles) return 0;

            let maxRank = 0;
            targetUser.roles.forEach((role: Role) => {
                const rank = ROLE_RANKS[role.name] || 0;
                if (rank > maxRank) maxRank = rank;
            });

            return maxRank;
        },

        // Check if current user has at least the specified role level
        isAtLeastRole: (state) => (roleName: string): boolean => {
            if (!state.user || !state.user.roles) return false;

            const minRank = ROLE_RANKS[roleName] || 0;

            let myRank = 0;
            state.user.roles.forEach((role: Role) => {
                const rank = ROLE_RANKS[role.name] || 0;
                if (rank > myRank) myRank = rank;
            });

            return myRank >= minRank;
        },

        // Check if current user has higher rank than another user
        isHigherThan: (state) => (otherUser: User): boolean => {
            if (!otherUser) return true;
            if (!state.user || !state.user.roles) return false;

            // Calculate my rank
            let myRank = 0;
            state.user.roles.forEach((role: Role) => {
                const rank = ROLE_RANKS[role.name] || 0;
                if (rank > myRank) myRank = rank;
            });

            // Calculate other user's rank
            let otherRank = 0;
            if (otherUser.roles) {
                otherUser.roles.forEach((role: Role) => {
                    const rank = ROLE_RANKS[role.name] || 0;
                    if (rank > otherRank) otherRank = rank;
                });
            }

            return myRank > otherRank;
        },

        isAdmin: (state): boolean => {
            if (!state.user || !state.user.roles) return false;
            return state.user.roles.some((role: Role) => 
                role.name === 'admin' || 
                (ROLE_RANKS[role.name] || 0) >= 100
            );
        },

        permissionNameSet: (state): Set<string> => {
            const names = state.user?.permissions?.map((perm) => perm.name) ?? [];
            return new Set(names);
        },

        hasPermission(): (permission: string) => boolean {
            return (permission: string): boolean => {
                if (!this.user) return false;
                if (this.user.roles?.some((role: Role) => (ROLE_RANKS[role.name] || 0) >= 100)) return true;
                return this.permissionNameSet.has(permission);
            };
        },
    },

    actions: {
        async login(credentials: LoginCredentials): Promise<AuthResponse> {
            try {
                // Ensure CSRF cookie is fresh before login
                await getCsrfCookie();

                const response: AxiosResponse<{ data?: unknown; user?: User; requires_two_factor?: boolean; user_id?: string; message?: string }> = await api.post('/public/system/auth/login', credentials, {
                    _schema: authResponseSchema
                } as ApiRequestConfig);
                // Handle different response structures
                const responseData = response.data;

                // Handle 2FA requirement
                if (responseData && responseData.requires_two_factor) {
                    return {
                        success: true,
                        requiresTwoFactor: true,
                        userId: responseData.user_id,
                        message: response.data.message
                    };
                }

                if (responseData && responseData.user) {
                    const authData: { user: User } = {
                        user: responseData.user
                    };
                    // Login response already Set-Cookie for new session + XSRF; do not hit csrf-cookie here (race → 401).
                    this.setAuth({ user: authData.user });
                    return { success: true, data: authData };
                } else {
                    throw new Error('Invalid response format from server');
                }
            } catch (error: unknown) {
                // Handle different error statuses
                const axiosError = isAxiosError(error) ? error : null;
                const errorData = (axiosError?.response?.data as any) || {};
                const errors = errorData.errors || errorData.data || {};
                const status = axiosError?.response?.status;
                const headers = axiosError?.response?.headers || {};

                // Handle rate limiting (429)
                if (status === 429) {
                    // Try to get retry-after from various sources
                    let retryAfter = 60; // Default 60 seconds

                    // Try from response body first
                    if (errorData.retry_after) {
                        retryAfter = parseInt(String(errorData.retry_after), 10);
                    }
                    // Try from headers (axios lowercases header names)
                    else if (headers['retry-after']) {
                        retryAfter = parseInt(String(headers['retry-after']), 10);
                    }

                    const retryAfterSeconds = retryAfter;
                    const retryAfterMinutes = Math.ceil(retryAfterSeconds / 60);

                    return {
                        success: false,
                        message: `Too many login attempts. Please try again in ${retryAfterMinutes} minute${retryAfterMinutes > 1 ? 's' : ''}.`,
                        errors: {},
                        rateLimited: true,
                        retryAfter: retryAfterSeconds,
                    };
                }

                // Handle different error statuses
                if (status === 403) {
                    // Email not verified
                    return {
                        success: false,
                        message: errorData.message || 'Please verify your email address before logging in.',
                        errors: {},
                        requiresVerification: errorData.requires_verification || false,
                    };
                }

                // Extract first error message if available
                let errorMessage = errorData.message;
                if (!errorMessage && errors.email && Array.isArray(errors.email) && errors.email.length > 0) {
                    errorMessage = errors.email[0];
                } else if (!errorMessage && errors.password && Array.isArray(errors.password) && errors.password.length > 0) {
                    errorMessage = errors.password[0];
                } else if (!errorMessage) {
                    errorMessage = 'Login failed. Please check your credentials.';
                }

                return {
                    success: false,
                    message: errorMessage,
                    errors: errors,
                };
            }
        },

        async register(userData: RegisterData): Promise<AuthResponse> {
            try {
                // Ensure CSRF cookie is fresh before register
                await getCsrfCookie();
                // Registration moved to member module in P10
                const response: AxiosResponse<{ user: User }> = await api.post('/public/member/register', userData);
                this.setAuth(response.data);
                return { success: true, data: response.data };
            } catch (error: unknown) {
                const axiosError = isAxiosError(error) ? error : null;
                const responseData = (axiosError?.response?.data as ApiErrorResponse) || {};
                return {
                    success: false,
                    message: responseData.message || 'Registration failed',
                    errors: responseData.errors,
                };
            }
        },

        async logout() {
            try {
                // Skip 401 handler redirect - logout is intentionally ending session
                await api.post('/public/system/auth/logout', {}, { _skipManualRedirect: true } as ApiRequestConfig);
            } catch (error: unknown) {
                // Silence session errors (401/419) and cancellations during logout
                // These are expected if the session is already terminated.
                const axiosError = isAxiosError(error) ? error : null;
                const status = axiosError?.response?.status;

                const isSilentError = status === 401 || status === 419 || isCancel(error);

                if (!isSilentError) {
                    logger.error('Logout error:', error);
                }
            } finally {
                this.clearAuth();
                // Reset all circuit breaker flags
                if (typeof window !== 'undefined') {
                    window.__isSessionTerminated = false;
                    window.__is403Blocked = false;
                }

                // Redirect to login page after logout
                window.location.href = SECURITY_ROUTES.login;
            }
        },

        async fetchUser(options?: { skipCsrfRefresh?: boolean }): Promise<AuthResponse> {
            const requestMe = async (): Promise<AuthResponse> => {
                const response: AxiosResponse<{ data?: User } | User> = await api.get('/public/system/auth/me', {
                    _schema: userModelSchema,
                    _skipManualRedirect: true,
                } as ApiRequestConfig);
                const userData = (response.data as any)?.data || response.data;
                this.user = userData as User;
                this.isAuthenticated = true;
                if (userData) {
                    this.setAuth({ user: userData as User });
                }
                return { success: true, data: { user: userData as User } };
            };

            const attempt = async (refreshCsrf: boolean): Promise<AuthResponse> => {
                if (refreshCsrf) {
                    await getCsrfCookie();
                }
                return await requestMe();
            };

            try {
                return await attempt(!options?.skipCsrfRefresh);
            } catch (error: unknown) {
                let finalError: unknown = error;
                const axiosError = isAxiosError(error) ? error : null;
                const status = axiosError?.response?.status;

                // Only retry on 419 CSRF mismatch, never on 401 (guest / logged out state)
                if (status === 419) {
                    try {
                        await getCsrfCookie();
                        return await requestMe();
                    } catch (retryError: unknown) {
                        finalError = retryError;
                    }
                }

                const retryAxios = isAxiosError(finalError) ? finalError : null;
                const retryStatus = retryAxios?.response?.status;

                if (retryStatus !== 403 && retryStatus !== 503) {
                    this.clearAuth();
                }

                const responseData = (retryAxios?.response?.data as ApiErrorResponse) || {};
                return { success: false, message: responseData.message };
            }
        },

        async forgotPassword(data: { email: string; captcha_token?: string; captcha_answer?: string }): Promise<AuthResponse> {
            try {
                const response: AxiosResponse<{ message: string }> = await api.post('/public/system/auth/forgot-password', data);
                return { success: true, message: response.data.message };
            } catch (error: unknown) {
                const axiosError = isAxiosError(error) ? error : null;
                const responseData = (axiosError?.response?.data as ApiErrorResponse) || {};
                return {
                    success: false,
                    message: responseData.message || 'Failed to send reset link',
                };
            }
        },

        async resetPassword(data: ResetPasswordData): Promise<AuthResponse> {
            try {
                const response: AxiosResponse<{ message: string }> = await api.post('/public/system/auth/reset-password', data);
                return { success: true, message: response.data.message };
            } catch (error: unknown) {
                const axiosError = isAxiosError(error) ? error : null;
                const responseData = (axiosError?.response?.data as ApiErrorResponse) || {};
                return {
                    success: false,
                    message: responseData.message || 'Password reset failed',
                    errors: responseData.errors,
                };
            }
        },

        setAuth(data: { user: User }) {
            this.user = data.user;
            this.isAuthenticated = true;
            localStorage.setItem('user', JSON.stringify(data.user));
            resetLockdown();
        },

        clearAuth() {
            this.user = null;
            this.isAuthenticated = false;
            localStorage.removeItem('user');
        },

        initAuth() {
            if (this.initialized) return;
            this.initialized = true;

            try {
                const user = localStorage.getItem('user');
                if (user) {
                    // Try to parse user JSON, clear if invalid
                    try {
                        const parsedUser = JSON.parse(user);
                        if (parsedUser && typeof parsedUser === 'object') {
                            // Hydrate display only — session validity comes from fetchUser()
                            this.user = parsedUser;
                            this.isAuthenticated = true;
                        } else {
                            this.clearAuth();
                        }
                    } catch (parseError) {
                        // Invalid JSON in localStorage, clear it
                if (appConfig.isDev) {
                            logger.warning('Invalid user data in localStorage, clearing:', parseError);
                        }
                        this.clearAuth();
                    }
                }
            } catch (error) {
                if (import.meta.env.DEV) {
                    logger.warning('Error initializing auth:', error);
                }
                this.clearAuth();
            }
        },
    },
});
