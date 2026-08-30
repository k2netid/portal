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

interface MemberAuthPayload {
    member: PublicMember;
    token: string;
}

interface MemberState {
    member: PublicMember | null;
    token: string | null;
    hydrated: boolean;
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

export const useMemberStore = defineStore('member', {
    state: (): MemberState => ({
        member: null,
        token: readStoredToken(),
        hydrated: false,
    }),

    getters: {
        isAuthenticated: (state): boolean => Boolean(state.token && state.member),
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
                this.member = response.data as PublicMember;
                this.hydrated = true;
            } catch {
                this.clear();
            }
        },

        async login(email: string, password: string): Promise<void> {
            const response = await api.post('/public/member/login', { email, password });
            this.applyAuth(response.data as MemberAuthPayload);
        },

        async register(input: {
            name: string;
            email: string;
            password: string;
            password_confirmation: string;
        }): Promise<void> {
            const response = await api.post('/public/member/register', input);
            this.applyAuth(response.data as MemberAuthPayload);
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
