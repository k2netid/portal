import { describe, expect, it, beforeEach } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { MEMBER_TOKEN_KEY } from '@/modules/Member/constants';
import { useMemberStore } from '@/modules/Member/stores/member';

describe('member store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        localStorage.removeItem(MEMBER_TOKEN_KEY);
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
});
