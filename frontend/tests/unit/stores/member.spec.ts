import { describe, expect, it, beforeEach, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { MEMBER_TOKEN_KEY } from '@/modules/Member/constants';
import { useMemberStore } from '@/modules/Member/stores/member';
import api from '@/engine/api/client';

vi.mock('@/engine/api/client', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
    },
}));

describe('member store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
        localStorage.removeItem(MEMBER_TOKEN_KEY);
        localStorage.removeItem('user');
    });

    it('starts signed out and does not use the console user key', () => {
        const store = useMemberStore();
        expect(store.isAuthenticated).toBe(false);
        expect(localStorage.getItem('user')).toBeNull();
        expect(localStorage.getItem(MEMBER_TOKEN_KEY)).toBeNull();
    });

    it('clears the reader token without touching console session storage', () => {
        localStorage.setItem('user', '{"id":"console"}');
        const store = useMemberStore();
        store.applyAuth({
            member: { id: 'm1', name: 'Reader', email: 'r@example.com', status: 'active' },
            token: 'member-token',
        });
        expect(localStorage.getItem(MEMBER_TOKEN_KEY)).toBe('member-token');
        store.clear();
        expect(store.isAuthenticated).toBe(false);
        expect(localStorage.getItem(MEMBER_TOKEN_KEY)).toBeNull();
        expect(localStorage.getItem('user')).toBe('{"id":"console"}');
    });

    it('hydrates member from API when stored token exists', async () => {
        localStorage.setItem(MEMBER_TOKEN_KEY, 'stored-token');
        const store = useMemberStore();

        vi.mocked(api.get)
            .mockResolvedValueOnce({
                data: {
                    data: { id: 'm10', name: 'Hydrated Member', email: 'hydrated@test.lan' },
                },
            })
            .mockResolvedValueOnce({
                data: {
                    data: {
                        member: { id: 'm10', name: 'Hydrated Member', email: 'hydrated@test.lan' },
                        capabilities: ['read', 'comment'],
                        active_extensions: ['blog'],
                        navigation: [],
                        widgets: [],
                    },
                },
            });

        await store.hydrate();
        expect(store.isAuthenticated).toBe(true);
        expect(store.member?.name).toBe('Hydrated Member');
        expect(store.hasCapability('comment')).toBe(true);
        expect(store.hasCapability('delete_post')).toBe(false);
        expect(store.portalCapabilities).toEqual(['read', 'comment']);
    });

    it('clears session when hydrate fails', async () => {
        localStorage.setItem(MEMBER_TOKEN_KEY, 'expired-token');
        const store = useMemberStore();

        vi.mocked(api.get).mockRejectedValueOnce(new Error('Unauthorized'));

        await store.hydrate();
        expect(store.isAuthenticated).toBe(false);
        expect(store.token).toBeNull();
    });

    it('handles login with standard flow and 2FA challenge flow', async () => {
        const store = useMemberStore();

        // 1. Standard login
        vi.mocked(api.post).mockResolvedValueOnce({
            data: {
                data: {
                    member: { id: 'm1', name: 'Logged In User', email: 'user@portal.net' },
                    token: 'tok-123',
                },
            },
        });
        vi.mocked(api.get).mockResolvedValueOnce({
            data: {
                data: {
                    member: { id: 'm1', name: 'Logged In User', email: 'user@portal.net' },
                    capabilities: [],
                    active_extensions: [],
                    navigation: [],
                    widgets: [],
                },
            },
        });

        const res1 = await store.login('user@portal.net', 'secret123');
        expect(res1.requires_two_factor).toBeFalsy();
        expect(store.isAuthenticated).toBe(true);
        expect(store.token).toBe('tok-123');

        // 2. Login requiring 2FA
        vi.mocked(api.post).mockResolvedValueOnce({
            data: {
                data: {
                    requires_two_factor: true,
                    member: { email: 'user@portal.net' },
                },
            },
        });

        const res2 = await store.login('user@portal.net', 'secret123');
        expect(res2.requires_two_factor).toBe(true);
    });

    it('registers a new member and updates portal state', async () => {
        const store = useMemberStore();

        vi.mocked(api.post).mockResolvedValueOnce({
            data: {
                data: {
                    member: { id: 'm2', name: 'New Reg', email: 'reg@portal.net' },
                    token: 'tok-reg',
                },
            },
        });
        vi.mocked(api.get).mockResolvedValueOnce({
            data: {
                data: {
                    member: { id: 'm2', name: 'New Reg', email: 'reg@portal.net' },
                    capabilities: ['read'],
                    active_extensions: [],
                    navigation: [],
                    widgets: [],
                },
            },
        });

        await store.register({
            name: 'New Reg',
            email: 'reg@portal.net',
            password: 'pass',
            password_confirmation: 'pass',
        });

        expect(store.isAuthenticated).toBe(true);
        expect(store.member?.name).toBe('New Reg');
    });

    it('updates profile and uploads avatar', async () => {
        const store = useMemberStore();
        store.applyAuth({
            member: { id: 'm1', name: 'Old Name', email: 'old@portal.net', status: 'active' },
            token: 'tok-1',
        });

        vi.mocked(api.patch).mockResolvedValueOnce({
            data: {
                data: { id: 'm1', name: 'Updated Name', email: 'old@portal.net' },
            },
        });

        await store.updateProfile({ name: 'Updated Name' });
        expect(store.member?.name).toBe('Updated Name');

        // Avatar upload
        const fakeFile = new File(['content'], 'avatar.png', { type: 'image/png' });
        vi.mocked(api.post).mockResolvedValueOnce({
            data: {
                data: { id: 'm1', name: 'Updated Name', email: 'old@portal.net', avatar: '/avatar.png' },
            },
        });

        const updated = await store.uploadAvatar(fakeFile);
        expect(updated.avatar).toBe('/avatar.png');
        expect(store.member?.avatar).toBe('/avatar.png');
    });

    it('manages two-factor actions (status, generate, verify, disable, backup codes)', async () => {
        const store = useMemberStore();

        // status
        vi.mocked(api.get).mockResolvedValueOnce({
            data: { data: { globally_enabled: true, enabled: false } },
        });
        const status = await store.fetchTwoFactorStatus();
        expect(status.globally_enabled).toBe(true);

        // generate
        vi.mocked(api.post).mockResolvedValueOnce({
            data: { data: { secret: 'SEC', qr_code_url: 'qr', backup_codes: ['code1'] } },
        });
        const gen = await store.generateTwoFactor();
        expect(gen.secret).toBe('SEC');

        // verify
        vi.mocked(api.post).mockResolvedValueOnce({
            data: { data: { globally_enabled: true, enabled: true } },
        });
        const verified = await store.verifyTwoFactor('123456');
        expect(verified.enabled).toBe(true);

        // regenerate backup codes
        vi.mocked(api.post).mockResolvedValueOnce({
            data: { data: { backup_codes: ['c2', 'c3'] } },
        });
        const codes = await store.regenerateTwoFactorBackupCodes('password');
        expect(codes.backup_codes).toEqual(['c2', 'c3']);

        // disable
        vi.mocked(api.post).mockResolvedValueOnce({ data: { success: true } });
        await expect(store.disableTwoFactor('password')).resolves.not.toThrow();
    });

    it('handles password operations and account deletion', async () => {
        const store = useMemberStore();
        store.applyAuth({
            member: { id: 'm1', name: 'User', email: 'u@portal.net', status: 'active' },
            token: 'tok-1',
        });

        vi.mocked(api.put).mockResolvedValueOnce({ data: { success: true } });
        await expect(
            store.updatePassword({
                current_password: 'old',
                password: 'new',
                password_confirmation: 'new',
            }),
        ).resolves.not.toThrow();

        vi.mocked(api.post).mockResolvedValueOnce({ data: { success: true } });
        await expect(store.forgotPassword('u@portal.net')).resolves.not.toThrow();

        vi.mocked(api.post).mockResolvedValueOnce({ data: { success: true } });
        await expect(
            store.resetPassword({
                email: 'u@portal.net',
                token: 'tok',
                password: 'new',
                password_confirmation: 'new',
            }),
        ).resolves.not.toThrow();

        // delete account
        vi.mocked(api.delete).mockResolvedValueOnce({ data: { success: true } });
        await store.deleteAccount({ current_password: 'pw', confirm: 'DELETE' });
        expect(store.isAuthenticated).toBe(false);
    });

    it('logs out and clears state', async () => {
        const store = useMemberStore();
        store.applyAuth({
            member: { id: 'm1', name: 'User', email: 'u@portal.net', status: 'active' },
            token: 'tok-1',
        });

        vi.mocked(api.post).mockResolvedValueOnce({ data: { success: true } });
        await store.logout();
        expect(store.isAuthenticated).toBe(false);
        expect(store.token).toBeNull();
    });
});
