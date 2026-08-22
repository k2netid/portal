import { defineStore } from 'pinia';
import api from '../api/client';
import { logger } from '@/shared/utils/logger';
import type { LoginCredentials } from '@/engine/types/auth';

interface User {
    id: string;
    name: string;
    email: string;
    roles: Array<{ name: string; guard_name: string }>;
    permissions: Array<{ name: string; guard_name: string }>;
}

interface AuthState {
    user: User | null;
    isAuthenticated: boolean;
    loading: boolean;
}

interface LoginResult {
    success: boolean;
    requiresTwoFactor?: boolean;
    userId?: number;
    message?: string;
    errors?: Record<string, string[]>;
    rateLimited?: boolean;
    retryAfter?: number;
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        user: null,
        isAuthenticated: false,
        loading: false,
    }),

    actions: {
        async login(credentials: LoginCredentials): Promise<LoginResult> {
            this.loading = true;
            try {
                const response = await api.post('/login', credentials) as {
                    requires_two_factor?: boolean;
                    user_id?: string | number;
                    user?: User;
                };
                
                // If backend says 2FA is needed
                if (response.requires_two_factor) {
                    const rawId = response.user_id;
                    return { 
                        success: true, 
                        requiresTwoFactor: true, 
                        userId: typeof rawId === 'number' ? rawId : (rawId != null ? Number(rawId) : undefined),
                    };
                }

                // Normal Success
                this.user = response.user ?? null;
                this.isAuthenticated = true;
                return { success: true };

            } catch (error: unknown) {
                const response = (error as {
                    response?: {
                        status?: number;
                        data?: { retry_after?: number; message?: string; errors?: Record<string, string[]> };
                    };
                }).response;
                
                if (response?.status === 429) {
                    return {
                        success: false,
                        rateLimited: true,
                        retryAfter: response.data?.retry_after,
                        message: response.data?.message
                    };
                }

                return {
                    success: false,
                    message: response?.data?.message || 'Login failed',
                    errors: response?.data?.errors
                };
            } finally {
                this.loading = false;
            }
        },

        async fetchUser() {
            this.loading = true;
            try {
                const response = await api.get('/public/system/auth/me');
                this.user = response.data;
                this.isAuthenticated = true;
            } catch (error) {
                this.user = null;
                this.isAuthenticated = false;
                logger.error('[AuthStore] Failed to fetch user profile', error);
            } finally {
                this.loading = false;
            }
        },

        logout() {
            this.user = null;
            this.isAuthenticated = false;
            localStorage.removeItem('auth');
            sessionStorage.clear();
        },

        hasRole(roleName: string): boolean {
            return this.user?.roles.some(r => r.name === roleName) ?? false;
        },

        getRoleRank(): number {
            if (!this.user) return 0;
            if (this.hasRole('super')) return 100;
            if (this.hasRole('admin')) return 90;
            if (this.hasRole('staff')) return 80;
            return 10;
        },

        hasPermission(permissionName: string): boolean {
            if (this.hasRole("super")) return true;
            return this.user?.permissions.some(p => p.name === permissionName) ?? false;
        },

        isAtLeastRole(minRole: string): boolean {
            const roleRanks: Record<string, number> = {
                'super': 100,
                'admin': 90,
                'staff': 80,
                'member': 10
            };
            const userRank = this.getRoleRank();
            const targetRank = roleRanks[minRole] || 0;
            return userRank >= targetRank;
        }
    },
    persist: true
});

