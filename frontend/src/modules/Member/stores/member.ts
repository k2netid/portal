import { defineStore } from 'pinia';
import api from '@/engine/api/client';
import { MEMBER_TOKEN_KEY } from '@/modules/Member/constants';
import type { MemberProfileInput, PublicMember } from '@/modules/Member/types/profile';

export type { PublicMember, MemberProfileInput } from '@/modules/Member/types/profile';

export interface MemberPortalNavItem {
    slug: string;
    label_key: string;
    route: string;
    order?: number;
    requires_verified?: boolean;
    capability?: string | null;
    extension_slug?: string;
}

export interface MemberPortalPayload {
    member: PublicMember;
    active_extensions: string[];
    capabilities: string[];
    navigation: MemberPortalNavItem[];
    widgets: Array<{
        slug: string;
        slot: string;
        order?: number;
        capability?: string | null;
        extension_slug?: string;
    }>;
}

interface MemberAuthPayload {
    member: PublicMember;
    token: string;
}

export interface MemberLoginOptions {
    captcha_token?: string;
    captcha_answer?: string;
    two_factor_code?: string;
}

export interface MemberLoginResult {
    requires_two_factor?: boolean;
    member?: PublicMember | { email?: string };
    token?: string;
    message?: string;
}

export interface MemberCaptchaFields {
    captcha_token?: string;
    captcha_answer?: string;
}

export interface MemberTwoFactorStatus {
    globally_enabled: boolean;
    enabled: boolean;
    enabled_at?: string | null;
    backup_codes_count?: number;
}

export interface MemberTwoFactorGenerateResult {
    secret: string;
    qr_code_url: string;
    backup_codes: string[];
}

interface MemberState {
    member: PublicMember | null;
    token: string | null;
    hydrated: boolean;
    portal: MemberPortalPayload | null;
}

const readStoredToken = (): string | null => {
    if (typeof localStorage === 'undefined') {
        return null;
    }
    return localStorage.getItem(MEMBER_TOKEN_KEY);
};

const persistToken = (token: string | null): void => {
    if (typeof localStorage === 'undefined') {
        return;
    }
    if (token) {
        localStorage.setItem(MEMBER_TOKEN_KEY, token);
    } else {
        localStorage.removeItem(MEMBER_TOKEN_KEY);
    }
};

const unwrapPayload = <T>(response: { data?: T | { data?: T } }): T => {
    const root = response.data;
    if (root && typeof root === 'object' && 'data' in root) {
        return (root as { data: T }).data;
    }
    return root as T;
};

export const useMemberStore = defineStore('member', {
    state: (): MemberState => ({
        member: null,
        token: readStoredToken(),
        hydrated: false,
        portal: null,
    }),

    getters: {
        isAuthenticated: (state): boolean => Boolean(state.token && state.member),
        portalCapabilities: (state): string[] => state.portal?.capabilities ?? [],
        hasCapability: (state) => (capability: string): boolean => (
            state.portal?.capabilities?.includes(capability) ?? false
        ),
    },

    actions: {
        applyAuth(payload: MemberAuthPayload): void {
            this.member = payload.member;
            this.token = payload.token;
            persistToken(payload.token);
            this.hydrated = true;
        },

        clear(): void {
            this.member = null;
            this.token = null;
            this.portal = null;
            persistToken(null);
            this.hydrated = true;
        },

        async hydrate(): Promise<void> {
            if (this.hydrated && this.member) {
                return;
            }
            const token = this.token || readStoredToken();
            if (!token) {
                this.clear();
                return;
            }
            this.token = token;
            persistToken(token);
            try {
                const response = await api.get('/member/me');
                this.member = unwrapPayload<PublicMember>(response);
                this.hydrated = true;
                await this.fetchPortal();
            } catch {
                this.clear();
            }
        },

        async fetchPortal(): Promise<void> {
            if (!this.token) {
                return;
            }
            try {
                const response = await api.get('/member/portal');
                this.portal = unwrapPayload<MemberPortalPayload>(response);
                if (this.portal?.member) {
                    this.member = this.portal.member;
                }
            } catch {
                this.portal = null;
            }
        },

        async login(
            email: string,
            password: string,
            options: MemberLoginOptions = {},
        ): Promise<MemberLoginResult> {
            const body: Record<string, string> = { email, password };
            if (options.captcha_token) {
                body.captcha_token = options.captcha_token;
            }
            if (options.captcha_answer) {
                body.captcha_answer = options.captcha_answer;
            }
            if (options.two_factor_code) {
                body.two_factor_code = options.two_factor_code;
            }

            const response = await api.post('/public/member/login', body);
            const payload = unwrapPayload<MemberLoginResult & Partial<MemberAuthPayload>>(response);

            if (payload.requires_two_factor) {
                return payload;
            }

            if (payload.member && payload.token && typeof payload.member === 'object' && 'id' in payload.member) {
                this.applyAuth({
                    member: payload.member as PublicMember,
                    token: payload.token,
                });
                await this.fetchPortal();
            }

            return payload;
        },

        async register(input: {
            name: string;
            email: string;
            password: string;
            password_confirmation: string;
            captcha_token?: string;
            captcha_answer?: string;
        }): Promise<void> {
            const response = await api.post('/public/member/register', input);
            this.applyAuth(unwrapPayload<MemberAuthPayload>(response));
            await this.fetchPortal();
        },

        async updateProfile(input: MemberProfileInput): Promise<void> {
            const response = await api.patch('/member/profile', input);
            this.member = unwrapPayload<PublicMember>(response);
            if (this.portal) {
                this.portal = { ...this.portal, member: this.member };
            }
        },

        async uploadAvatar(file: File): Promise<PublicMember> {
            const body = new FormData();
            body.append('file', file);
            const response = await api.post('/member/profile/avatar', body, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            const member = unwrapPayload<PublicMember>(response);
            this.member = member;
            if (this.portal) {
                this.portal = { ...this.portal, member };
            }
            return member;
        },

        async updatePassword(input: {
            current_password: string;
            password: string;
            password_confirmation: string;
        }): Promise<void> {
            await api.put('/member/password', input);
        },

        async forgotPassword(email: string, captcha?: MemberCaptchaFields): Promise<void> {
            await api.post('/public/member/forgot-password', {
                email,
                ...(captcha?.captcha_token ? { captcha_token: captcha.captcha_token } : {}),
                ...(captcha?.captcha_answer ? { captcha_answer: captcha.captcha_answer } : {}),
            });
        },

        async resetPassword(input: {
            email: string;
            token: string;
            password: string;
            password_confirmation: string;
        }): Promise<void> {
            await api.post('/public/member/reset-password', input);
        },

        async requestEmailChange(input: {
            email: string;
            current_password: string;
        }): Promise<string> {
            const response = await api.put('/member/email', input);
            const payload = unwrapPayload<{ pending_email?: string }>(response);
            if (this.member) {
                this.member = {
                    ...this.member,
                    pending_email: payload.pending_email ?? input.email,
                };
            }
            return payload.pending_email ?? input.email;
        },

        async deleteAccount(input: {
            current_password: string;
            confirm: 'DELETE';
        }): Promise<void> {
            await api.delete('/member/account', { data: input });
            this.clear();
        },

        async resendVerification(): Promise<void> {
            await api.post('/member/email/verification-notification');
            if (this.member) {
                this.member = { ...this.member };
            }
        },

        async fetchTwoFactorStatus(): Promise<MemberTwoFactorStatus> {
            const response = await api.get('/member/2fa/status');
            return unwrapPayload<MemberTwoFactorStatus>(response);
        },

        async generateTwoFactor(): Promise<MemberTwoFactorGenerateResult> {
            const response = await api.post('/member/2fa/generate');
            return unwrapPayload<MemberTwoFactorGenerateResult>(response);
        },

        async verifyTwoFactor(code: string): Promise<MemberTwoFactorStatus> {
            const response = await api.post('/member/2fa/verify', { code });
            return unwrapPayload<MemberTwoFactorStatus>(response);
        },

        async disableTwoFactor(password: string): Promise<void> {
            await api.post('/member/2fa/disable', { password });
        },

        async regenerateTwoFactorBackupCodes(password: string): Promise<{ backup_codes: string[]; backup_codes_count?: number }> {
            const response = await api.post('/member/2fa/regenerate-backup-codes', { password });
            return unwrapPayload<{ backup_codes: string[]; backup_codes_count?: number }>(response);
        },

        async logout(): Promise<void> {
            try {
                await api.post('/member/logout', {}, { _skipManualRedirect: true } as never);
            } catch {
                /* token already invalid */
            }
            this.clear();
        },
    },
});
