import { defineStore } from 'pinia';
import api from '@/engine/api/client';
import { MEMBER_TOKEN_KEY } from '@/modules/Member/constants';

export interface PublicMember {
    id: string;
    name: string;
    email: string;
    status: string;
    email_verified?: boolean;
}

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

        async login(email: string, password: string): Promise<void> {
            const response = await api.post('/public/member/login', { email, password });
            this.applyAuth(unwrapPayload<MemberAuthPayload>(response));
            await this.fetchPortal();
        },

        async register(input: {
            name: string;
            email: string;
            password: string;
            password_confirmation: string;
        }): Promise<void> {
            const response = await api.post('/public/member/register', input);
            this.applyAuth(unwrapPayload<MemberAuthPayload>(response));
            await this.fetchPortal();
        },

        async updateProfile(name: string): Promise<void> {
            const response = await api.patch('/member/profile', { name });
            this.member = unwrapPayload<PublicMember>(response);
            if (this.portal) {
                this.portal = { ...this.portal, member: this.member };
            }
        },

        async updatePassword(input: {
            current_password: string;
            password: string;
            password_confirmation: string;
        }): Promise<void> {
            await api.put('/member/password', input);
        },

        async resendVerification(): Promise<void> {
            await api.post('/member/email/verification-notification');
            if (this.member) {
                this.member = { ...this.member };
            }
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
