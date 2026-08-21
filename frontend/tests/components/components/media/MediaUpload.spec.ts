import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import MediaUpload from '@/shared/components/ui/MediaUpload.vue';
import { createTestingPinia } from '@pinia/testing';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { createI18n } from 'vue-i18n';

vi.mock('@/engine/api/client', () => ({
    default: {
        post: vi.fn()
    }
}));

vi.mock('@/shared/composables/useToast', () => ({
    useToast: vi.fn()
}));

vi.mock('@/shared/utils/logger', () => ({
    logger: {
        error: vi.fn(),
        debug: vi.fn()
    }
}));

vi.mock('@/modules/Core/System/stores/system', async () => {
    const { ref } = await import('vue');
    return {
        useSystemStore: () => ({
            settings: ref({ max_upload_size: 2048 }),
            fetchSettingsGroup: vi.fn().mockResolvedValue({}),
            loadingGroups: {},
            settingsPromises: {},
        }),
    };
});

const i18n = createI18n({
    legacy: false,
    locale: 'en',
    messages: {
        en: {
            features: {
                media: {
                    modals: {
                        upload: {
                            errorLoadingImage: 'Error loading image'
                        }
                    }
                }
            }
        }
    }
});

describe('MediaUpload.vue', () => {
    let mockToast: any;

    beforeEach(() => {
        vi.clearAllMocks();

        mockToast = {
            error: { validation: vi.fn() }
        };
        vi.mocked(useToast).mockReturnValue(mockToast);

        // Mock window.URL
        window.URL.createObjectURL = vi.fn(() => 'blob:test');
        window.URL.revokeObjectURL = vi.fn();

        // Stable Image Mock
        const MockImage = function (this: any) {
            this.width = 800;
            this.height = 600;
            this.onload = null;
            this.onerror = null;
            let _src = '';
            Object.defineProperty(this, 'src', {
                get() { return _src; },
                set(val: string) {
                    _src = val;
                    Promise.resolve().then(() => {
                        if ((window as any)._failImage) {
                            this.onerror?.(new Event('error'));
                        } else {
                            this.onload?.(new Event('load'));
                        }
                    });
                }
            });
        };
        vi.stubGlobal('Image', MockImage);
        (window as any)._failImage = false;
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    const createWrapper = (props = {}) => {
        return mount(MediaUpload, {
            props,
            global: {
                plugins: [
                    i18n,
                    createTestingPinia({
                        initialState: {
                            system: {
                                settings: { max_upload_size: 2048 },
                            },
                        },
                        createSpy: vi.fn,
                    })
                ]
            }
        });
    };

    const selectMockFile = async (wrapper: any, fail = false) => {
        (window as any)._failImage = fail;
        const input = wrapper.find('input[type="file"]');
        const file = new File(['test'], 'test.jpg', { type: 'image/jpeg' });
        Object.defineProperty(input.element, 'files', { value: [file] });
        await input.trigger('change');

        await flushPromises();
        if (vi.isFakeTimers()) {
            await vi.runAllTimersAsync();
        }
        await flushPromises();
        await (wrapper.vm as any).$nextTick();
        return file;
    };

    it('renders upload area', () => {
        const wrapper = createWrapper();
        expect(wrapper.find('input[type="file"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('media.modals.upload.chooseFile');
    });

    it('triggers file select dialog', async () => {
        const wrapper = createWrapper();
        const input = wrapper.find('input[type="file"]');
        const clickSpy = vi.spyOn(input.element as any, 'click');
        await (wrapper.find('button') as any).trigger('click');
        expect(clickSpy).toHaveBeenCalled();
    });

    it('validates file size', async () => {
        const wrapper = createWrapper();
        const input = wrapper.find('input[type="file"]');
        const largeFile = new File([new ArrayBuffer(3 * 1024 * 1024)], 'large.jpg', { type: 'image/jpeg' });
        Object.defineProperty(largeFile, 'size', { value: 3 * 1024 * 1024 });
        Object.defineProperty(input.element, 'files', { value: [largeFile] });
        await input.trigger('change');
        await flushPromises();
        expect(mockToast.error.validation).toHaveBeenCalled();
    });

    it('handles image loading error', async () => {
        const wrapper = createWrapper();
        await selectMockFile(wrapper, true);
        expect(mockToast.error.validation).toHaveBeenCalled();
    });

    it('handles successful file selection and preview generation', async () => {
        const wrapper = createWrapper();
        await selectMockFile(wrapper);
        expect(wrapper.find('img').exists()).toBe(true);
    });

    it('uploads file successfully', async () => {
        vi.useFakeTimers();
        const wrapper = createWrapper({ folderId: 10 });
        await selectMockFile(wrapper);

        vi.mocked(api.post).mockResolvedValueOnce({
            data: { data: { id: "1", url: 'https://test/test.jpg' } }
        });

        const uploadBtn = wrapper.find('button.bg-green-600');
        await (uploadBtn as any).trigger('click');
        expect(api.post).toHaveBeenCalled();

        await flushPromises();
        expect(wrapper.emitted('uploaded')).toHaveLength(1);

        vi.advanceTimersByTime(3000);
        await flushPromises();
        expect(wrapper.find('input[type="file"]').exists()).toBe(true);
    });

    it('handles upload error', async () => {
        const wrapper = createWrapper();
        await selectMockFile(wrapper);
        vi.mocked(api.post).mockRejectedValueOnce({
            response: { data: { message: 'Upload Failed' } }
        });
        await (wrapper.find('button.bg-green-600') as any).trigger('click');
        await flushPromises();
        expect(wrapper.text()).toContain('Upload Failed');
    });

    it('validates image dimensions (minWidth)', async () => {
        const wrapper = createWrapper({
            constraints: { minWidth: 1000 }
        });
        await selectMockFile(wrapper); // MockImage defaults to 800x600
        expect(mockToast.error.validation).toHaveBeenCalledWith(expect.stringContaining('width must be at least 1000px'));
    });

    it('validates image dimensions (minHeight)', async () => {
        const wrapper = createWrapper({
            constraints: { minHeight: 700 }
        });
        await selectMockFile(wrapper);
        expect(mockToast.error.validation).toHaveBeenCalledWith(expect.stringContaining('height must be at least 700px'));
    });

    it('validates image dimensions (maxWidth)', async () => {
        const wrapper = createWrapper({
            constraints: { maxWidth: 500 }
        });
        await selectMockFile(wrapper);
        expect(mockToast.error.validation).toHaveBeenCalledWith(expect.stringContaining('width exceeds limit of 500px'));
    });

    it('validates image dimensions (maxHeight)', async () => {
        const wrapper = createWrapper({
            constraints: { maxHeight: 500 }
        });
        await selectMockFile(wrapper);
        expect(mockToast.error.validation).toHaveBeenCalledWith(expect.stringContaining('height exceeds limit of 500px'));
    });

    it('handles non-image files', async () => {
        const wrapper = createWrapper();
        const input = wrapper.find('input[type="file"]');
        const file = new File(['test'], 'test.pdf', { type: 'application/pdf' });
        Object.defineProperty(input.element, 'files', { value: [file] });
        await input.trigger('change');
        await flushPromises();
        expect(wrapper.find('video').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(false);
    });

    it('clears preview manually', async () => {
        const wrapper = createWrapper();
        await selectMockFile(wrapper);
        await (wrapper.find('button.bg-muted') as any).trigger('click');
        expect(window.URL.revokeObjectURL).toHaveBeenCalled();
        expect(wrapper.find('input[type="file"]').exists()).toBe(true);
    });
});
