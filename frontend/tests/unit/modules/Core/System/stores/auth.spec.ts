import { describe, it, expect, vi, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useAuthStore, ROLE_RANKS } from '@/modules/Core/System/stores/auth';
import api, { getCsrfCookie } from '@/engine/api/client';

vi.mock('@/engine/api/client', () => ({
    default: {
        post: vi.fn(),
        get: vi.fn(),
    },
    getCsrfCookie: vi.fn(),
    resetLockdown: vi.fn(),
}));

vi.mock('@/shared/utils/logger');

vi.mock('axios', async () => {
    const actual = await vi.importActual('axios');
    return {
        ...actual,
        isAxiosError: vi.fn((e) => e && e.isAxiosError === true),
    };
});

describe('Auth Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
        localStorage.clear();

        // Mock window properties if needed
        (global as any).window = {
            __isSessionTerminated: false,
            __is403Blocked: false,
        };
    });

    it('initializes with default state', () => {
        const store = useAuthStore();
        expect(store.user).toBeNull();
        expect(store.isAuthenticated).toBe(false);
    });

    describe('Getters', () => {
        const mockUser = {
            id: "1",
            name: 'Admin',
            roles: [{ name: 'admin' }],
            permissions: [{ name: 'edit content' }]
        };

        it(' isAdmin returns true for admin or super', () => {
            const store = useAuthStore();
            store.user = mockUser as any;
            expect(store.isAdmin).toBe(true);

            store.user = { roles: [{ name: 'super' }] } as any;
            expect(store.isAdmin).toBe(true);

            store.user = { roles: [{ name: 'member' }] } as any;
            expect(store.isAdmin).toBe(false);
        });

        it('hasPermission returns true if user has permission', () => {
            const store = useAuthStore();
            store.user = mockUser as any;
            expect(store.hasPermission('edit content')).toBe(true);
            expect(store.hasPermission('delete content')).toBe(false);
        });

        it('hasPermission returns true for super', () => {
            const store = useAuthStore();
            store.user = { roles: [{ name: 'super' }] } as any;
            expect(store.hasPermission('anything')).toBe(true);
        });

        it('getRoleRank calculates correctly', () => {
            const store = useAuthStore();
            store.user = mockUser as any;
            expect(store.getRoleRank()).toBe(ROLE_RANKS['admin']);

            const otherUser = { roles: [{ name: 'super' }] } as any;
            expect(store.getRoleRank(otherUser)).toBe(ROLE_RANKS['super']);
        });

        it('isAtLeastRole checks rank correctly', () => {
            const store = useAuthStore();
            store.user = mockUser as any;
            expect(store.isAtLeastRole('editor')).toBe(true);
            expect(store.isAtLeastRole('super')).toBe(false);
        });

        it('isHigherThan compares roles correctly', () => {
            const store = useAuthStore();
            store.user = { roles: [{ name: 'admin' }] } as any;
            const otherUser = { roles: [{ name: 'editor' }] } as any;
            expect(store.isHigherThan(otherUser)).toBe(true);

            const superiorUser = { roles: [{ name: 'super' }] } as any;
            expect(store.isHigherThan(superiorUser)).toBe(false);
        });
    });

    describe('Actions', () => {
        it('initAuth restores user from localStorage', () => {
            const userData = { id: "1", name: 'Test' };
            localStorage.setItem('user', JSON.stringify(userData));

            const store = useAuthStore();
            store.initAuth();

            expect(store.user).toEqual(userData);
            expect(store.isAuthenticated).toBe(true);
        });

        it('login handles successful authentication', async () => {
            const store = useAuthStore();
            const userData = { user: { id: "1", name: 'Admin' } };

            vi.mocked(api.post).mockResolvedValueOnce({
                data: userData,
            });

            const result = await store.login({ email: 'test@example.com', password: 'password' });

            expect(getCsrfCookie).toHaveBeenCalled();
            expect(store.user).toEqual(userData.user);
            expect(store.isAuthenticated).toBe(true);
            expect(result.success).toBe(true);
            expect(localStorage.getItem('user')).toContain('Admin');
        });

        it('login handles 2FA requirement', async () => {
            const store = useAuthStore();
            vi.mocked(api.post).mockResolvedValueOnce({
                data: { requires_two_factor: true, user_id: '123' },
            });

            const result = await store.login({ email: 'test@example.com', password: 'password' });

            expect(result.requiresTwoFactor).toBe(true);
            expect(result.userId).toBe('123');
            expect(store.isAuthenticated).toBe(false);
        });

        it('login handles validation errors with top-level message', async () => {
            const store = useAuthStore();
            const error: any = new Error();
            error.isAxiosError = true;
            error.response = {
                status: 422,
                data: { message: 'Invalid credentials', errors: { email: ['Wrong email'] } }
            };

            vi.mocked(api.post).mockRejectedValueOnce(error);

            const result = await store.login({ email: 'test@example.com', password: 'password' });

            expect(result.success).toBe(false);
            expect(result.message).toBe('Invalid credentials');
            expect(result.errors).toEqual({ email: ['Wrong email'] });
        });

        it('login handles validation errors with fallback to first error', async () => {
            const store = useAuthStore();
            const error: any = new Error();
            error.isAxiosError = true;
            error.response = {
                status: 422,
                data: { errors: { email: ['Wrong email'] } }
            };

            vi.mocked(api.post).mockRejectedValueOnce(error);

            const result = await store.login({ email: 'test@example.com', password: 'password' });

            expect(result.success).toBe(false);
            expect(result.message).toBe('Wrong email');
        });

        it('login handles rate limiting', async () => {
            const store = useAuthStore();
            const error: any = new Error();
            error.isAxiosError = true;
            error.response = {
                status: 429,
                headers: { 'retry-after': '120' },
                data: {}
            };

            vi.mocked(api.post).mockRejectedValueOnce(error);

            const result = await store.login({ email: 'test@example.com', password: 'password' });

            expect(result.rateLimited).toBe(true);
            expect(result.retryAfter).toBe(120);
            expect(result.message).toContain('2 minutes');
        });

        it('logout clears state and localStorage', async () => {
            const store = useAuthStore();
            store.user = { id: "1" } as any;
            store.isAuthenticated = true;

            const locationAssign = vi.fn();
            Object.defineProperty(window, 'location', {
                configurable: true,
                value: { href: '/', assign: locationAssign },
            });

            await store.logout();

            expect(api.post).toHaveBeenCalledWith('/public/system/auth/logout', {}, expect.anything());
            expect(store.user).toBeNull();
            expect(store.isAuthenticated).toBe(false);
            expect(localStorage.getItem('user')).toBeNull();
        });

        it('fetchUser updates current user data', async () => {
            const store = useAuthStore();
            const userData = { id: "1", name: 'Fetched' };

            vi.mocked(api.get).mockResolvedValueOnce({
                data: { data: userData },
            });

            await store.fetchUser();

            expect(api.get).toHaveBeenCalledWith('/public/system/auth/me', expect.anything());
            expect(store.user).toEqual(userData);
            expect(store.isAuthenticated).toBe(true);
        });

        it('register handles success', async () => {
            const store = useAuthStore();
            const userData = { user: { id: "1", name: 'New User' } };
            vi.mocked(api.post).mockResolvedValueOnce({ data: userData });

            const result = await store.register({ name: 'New', email: 'n@e.c', password: 'p' } as any);

            expect(result.success).toBe(true);
            expect(store.user).toEqual(userData.user);
        });

        it('forgotPassword handles success', async () => {
            const store = useAuthStore();
            vi.mocked(api.post).mockResolvedValueOnce({ data: { message: 'Sent' } });

            const result = await store.forgotPassword({ email: 'test@e.c' });

            expect(result.success).toBe(true);
            expect(result.message).toBe('Sent');
        });

        it('resetPassword handles success', async () => {
            const store = useAuthStore();
            vi.mocked(api.post).mockResolvedValueOnce({ data: { message: 'Reset' } });

            const result = await store.resetPassword({ email: 'test@e.c' } as any);

            expect(result.success).toBe(true);
            expect(result.message).toBe('Reset');
        });

        it('initAuth handles generic initialization error', () => {
            vi.spyOn(localStorage, 'getItem').mockImplementationOnce(() => { throw new Error('Storage error'); });
            const store = useAuthStore();
            store.initAuth();
            expect(store.user).toBeNull();
        });

        it('login handles network/generic error', async () => {
            const store = useAuthStore();
            vi.mocked(api.post).mockRejectedValueOnce(new Error('Network Error'));
            const result = await store.login({ email: 'a@b.c', password: 'p' });
            expect(result.success).toBe(false);
            expect(result.message).toBe('Login failed. Please check your credentials.');
        });

        it('register handles error', async () => {
            const store = useAuthStore();
            vi.mocked(api.post).mockRejectedValueOnce(new Error('Fail'));
            const result = await store.register({} as any);
            expect(result.success).toBe(false);
        });

        it('resetPassword handles error', async () => {
            const store = useAuthStore();
            vi.mocked(api.post).mockRejectedValueOnce(new Error('Fail'));
            const result = await store.resetPassword({} as any);
            expect(result.success).toBe(false);
        });
    });
});
