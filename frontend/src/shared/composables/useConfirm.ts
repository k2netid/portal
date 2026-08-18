import { ref } from 'vue';
import i18n from '@/engine/i18n';

export interface ConfirmOptions {
    title: string;
    message?: string; // Legacy support
    description?: string;
    variant?: 'warning' | 'danger' | 'destructive' | 'info' | 'question' | 'success';
    confirmText?: string;
    cancelText?: string;
    input?: boolean;
    inputPlaceholder?: string;
    checkbox?: boolean;
    checkboxLabel?: string;
    checkboxDefault?: boolean;
}

interface ConfirmState extends ConfirmOptions {
    isOpen: boolean;
    onConfirm: (val: any) => void;
    onCancel: () => void;
}

const t = (key: string) => i18n.global.t(key);

const confirmState = ref<ConfirmState>({
    isOpen: false,
    title: t('common.messages.confirm.defaultTitle'),
    message: '',
    description: '',
    variant: 'warning',
    confirmText: t('common.messages.confirm.defaultConfirm'),
    cancelText: t('common.messages.confirm.defaultCancel'),
    checkbox: false,
    checkboxLabel: '',
    checkboxDefault: false,
    onConfirm: () => { },
    onCancel: () => { }
});

export function useConfirm() {
    const confirm = (options: ConfirmOptions): Promise<string | boolean> => {
        return new Promise((resolve) => {
            confirmState.value = {
                isOpen: true,
                title: options.title || t('common.messages.confirm.defaultTitle'),
                message: options.message || '',
                description: options.description || '',
                variant: options.variant || 'warning',
                confirmText: options.confirmText || t('common.messages.confirm.defaultConfirm'),
                cancelText: options.cancelText || t('common.messages.confirm.defaultCancel'),
                input: options.input || false,
                checkbox: options.checkbox || false,
                checkboxLabel: options.checkboxLabel || '',
                checkboxDefault: options.checkboxDefault || false,
                onConfirm: (val?: any) => {
                    resolve(options.input || options.checkbox ? (val ?? '') : true);
                    confirmState.value.isOpen = false;
                },
                onCancel: () => {
                    resolve(false);
                    confirmState.value.isOpen = false;
                },
            };
        });
    };

    return {
        confirm,
        confirmState,
    };
}
