import { logger } from '@/shared/utils/logger';
import i18n from '@/engine/i18n';

const t = (key: string) => i18n.global.t(key);

export interface ToastOptions {
    title: string;
    description?: string;
    variant?: 'default' | 'destructive' | 'success' | 'warning' | 'info' | 'error';
    duration?: number;
    action?: {
        label: string;
        onClick: () => void;
    };
}

export interface ToastInstance {
    addToast: (options: ToastOptions) => void;
    removeToast?: (id: string | number) => void;
    [key: string]: unknown;
}

let toastInstance: ToastInstance | null = null;

export const setToastInstance = (instance: ToastInstance) => {
    toastInstance = instance;
    if (typeof window !== 'undefined') {
        (window as unknown as { __toastInstance: ToastInstance }).__toastInstance = instance;
    }
};

export const toast = {
    show(options: ToastOptions) {
        const instance = toastInstance || (typeof window !== 'undefined' ? (window as unknown as { __toastInstance: ToastInstance }).__toastInstance : null);
        if (instance?.addToast) {
            return instance.addToast(options);
        }
        logger.warning('Toast not initialized:', options);
        return null;
    },

    success(title: string, description: string = '') {
        return this.show({ title, description, variant: 'success' });
    },

    error(title: string, description: string = '') {
        return this.show({ title, description, variant: 'destructive' });
    },

    warning(title: string, description: string = '') {
        return this.show({ title, description, variant: 'warning' });
    },

    info(title: string, description: string = '') {
        return this.show({ title, description, variant: 'info' });
    },

    sessionExpired(message?: string) {
        return this.show({
            title: t('common.messages.session.expiredTitle'),
            description: message || t('common.messages.session.terminatedDescription'),
            variant: 'warning',
            duration: 5000,
        });
    },
};

export default toast;
