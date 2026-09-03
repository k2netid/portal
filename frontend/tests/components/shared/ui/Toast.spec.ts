import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import Toast from '@/shared/components/ui/Toast.vue';

describe('Toast.vue', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.restoreAllMocks();
    });

    it('mounts and registers global toast instance on window', async () => {
        const wrapper = mount(Toast, {
            attachTo: document.body,
        });

        const instance = (window as any).__toastInstance;
        expect(instance).toBeDefined();
        expect(typeof instance.addToast).toBe('function');
        expect(typeof instance.removeToast).toBe('function');

        // Add a default toast
        const id1 = instance.addToast({
            title: 'Info Notice',
            description: 'Operation completed',
            variant: 'info',
            duration: 3000,
        });
        expect(id1).toBeDefined();

        await wrapper.vm.$nextTick();
        expect(document.body.textContent).toContain('Info Notice');
        expect(document.body.textContent).toContain('Operation completed');

        // Add an error toast
        instance.addToast({
            title: 'Failure',
            description: 'Something broke',
            variant: 'error',
            duration: 0, // no auto dismiss
        });

        await wrapper.vm.$nextTick();
        expect(document.body.textContent).toContain('Failure');

        // Test auto-dismiss with timer
        vi.advanceTimersByTime(3100);
        await wrapper.vm.$nextTick();
        expect(document.body.textContent).not.toContain('Info Notice');

        // Test manual removal
        instance.removeToast(id1);
        wrapper.unmount();
    });

    it('supports all toast variants (success, warning, error, default)', async () => {
        const wrapper = mount(Toast, {
            attachTo: document.body,
        });
        const instance = (window as any).__toastInstance;

        ['success', 'warning', 'error', 'default'].forEach((variant) => {
            instance.addToast({
                title: `${variant} toast`,
                variant,
                duration: 0,
            });
        });

        await wrapper.vm.$nextTick();
        expect(document.body.textContent).toContain('success toast');
        expect(document.body.textContent).toContain('warning toast');
        expect(document.body.textContent).toContain('error toast');

        wrapper.unmount();
    });
});
