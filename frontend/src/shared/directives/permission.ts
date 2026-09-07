import type { Directive, DirectiveBinding } from 'vue';
import { useAuthStore } from '@/modules/Core/System/stores/auth';

function checkPermission(binding: DirectiveBinding): boolean {
    const authStore = useAuthStore();
    const value = binding.value;
    if (!value) return true;

    if (Array.isArray(value)) {
        if (binding.modifiers.any) {
            return value.some((perm: string) => authStore.hasPermission(perm));
        }
        return value.every((perm: string) => authStore.hasPermission(perm));
    }

    return authStore.hasPermission(String(value));
}

function checkRole(binding: DirectiveBinding): boolean {
    const authStore = useAuthStore();
    const value = binding.value;
    if (!value) return true;

    if (Array.isArray(value)) {
        if (binding.modifiers.any) {
            return value.some((role: string) => authStore.hasRole(role));
        }
        return value.every((role: string) => authStore.hasRole(role));
    }

    return authStore.hasRole(String(value));
}

export const vCan: Directive<HTMLElement> = {
    mounted(el, binding) {
        const hasAccess = checkPermission(binding);
        if (!hasAccess) {
            if (binding.modifiers.disabled) {
                el.setAttribute('disabled', 'true');
                el.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            } else {
                el.parentNode?.removeChild(el);
            }
        }
    },
    updated(el, binding) {
        const hasAccess = checkPermission(binding);
        if (!hasAccess) {
            if (binding.modifiers.disabled) {
                el.setAttribute('disabled', 'true');
                el.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            } else {
                el.parentNode?.removeChild(el);
            }
        } else if (binding.modifiers.disabled) {
            el.removeAttribute('disabled');
            el.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        }
    },
};

export const vRole: Directive<HTMLElement> = {
    mounted(el, binding) {
        const hasAccess = checkRole(binding);
        if (!hasAccess) {
            if (binding.modifiers.disabled) {
                el.setAttribute('disabled', 'true');
                el.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            } else {
                el.parentNode?.removeChild(el);
            }
        }
    },
    updated(el, binding) {
        const hasAccess = checkRole(binding);
        if (!hasAccess) {
            if (binding.modifiers.disabled) {
                el.setAttribute('disabled', 'true');
                el.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            } else {
                el.parentNode?.removeChild(el);
            }
        } else if (binding.modifiers.disabled) {
            el.removeAttribute('disabled');
            el.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        }
    },
};
