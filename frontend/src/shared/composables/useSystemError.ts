import { reactive, readonly } from 'vue';

export interface SystemError {
    isVisible: boolean;
    code: number | null;
    title: string;
    message: string;
    description: string;
    reason: string | null;
    redirect: string | null;
    traceId: string;
}

export interface ShowErrorParams {
    code: number;
    title: string;
    message: string;
    description: string;
    reason?: string | null;
    redirect?: string | null;
}

const state = reactive<SystemError>({
    isVisible: false,
    code: null,
    title: '',
    message: '',
    description: '',
    reason: null,
    redirect: null,
    traceId: '',
});

export function useSystemError() {
    const showError = ({ code, title, message, description, reason = null, redirect = null }: ShowErrorParams) => {
        state.code = code;
        state.title = title;
        state.message = message;
        state.description = description;
        state.reason = reason;
        state.redirect = redirect;
        state.traceId = `TRC-${Date.now().toString().slice(-6)}-${Math.random().toString(36).substring(7).toUpperCase()}`;
        state.isVisible = true;

        // Clear all pending network requests and activity if it's a critical error
        if (code === 401 || code === 419) {
            if (typeof window !== 'undefined' && typeof window.stop === 'function') {
                window.stop();
            }
        }
    };

    const hideError = () => {
        state.isVisible = false;
        // Reset state after transition
        setTimeout(() => {
            if (!state.isVisible) {
                state.code = null;
                state.title = '';
                state.message = '';
                state.description = '';
                state.reason = null;
                state.redirect = null;
                state.traceId = '';
            }
        }, 300);
    };

    return {
        state: readonly(state),
        showError,
        hideError,
    };
}
