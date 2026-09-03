import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import LazyImage from '@/shared/components/ui/LazyImage.vue';

describe('LazyImage.vue', () => {
    let observeMock: ReturnType<typeof vi.fn>;
    let unobserveMock: ReturnType<typeof vi.fn>;

    beforeEach(() => {
        observeMock = vi.fn();
        unobserveMock = vi.fn();

        class MockIntersectionObserver {
            constructor(callback: any) {
                (window as any).__triggerIntersection = callback;
            }
            observe = observeMock;
            unobserve = unobserveMock;
            disconnect = vi.fn();
        }

        (window as any).IntersectionObserver = MockIntersectionObserver;
    });

    it('renders with placeholder and data-src attribute', () => {
        const wrapper = mount(LazyImage, {
            props: {
                src: 'https://example.com/banner.jpg',
                alt: 'Banner',
            },
        });

        const img = wrapper.find('img');
        expect(img.attributes('data-src')).toBe('https://example.com/banner.jpg');
        expect(img.attributes('alt')).toBe('Banner');
        expect(observeMock).toHaveBeenCalled();
    });

    it('loads thumbnail immediately without waiting for observer', () => {
        const wrapper = mount(LazyImage, {
            props: {
                src: 'https://example.com/thumbnails/photo_thumb.jpg',
                alt: 'Thumb',
            },
        });

        const img = wrapper.find('img');
        expect(img.attributes('src')).toBe('https://example.com/thumbnails/photo_thumb.jpg');
    });

    it('triggers load on intersection', async () => {
        const wrapper = mount(LazyImage, {
            props: {
                src: 'https://example.com/large.png',
            },
        });

        // Trigger intersection callback
        const callback = (window as any).__triggerIntersection;
        if (callback) {
            callback([{ isIntersecting: true }]);
        }

        const img = wrapper.find('img');
        expect(img.attributes('src')).toBe('https://example.com/large.png');
        expect(unobserveMock).toHaveBeenCalled();
    });

    it('handles image load event and emits load', async () => {
        const wrapper = mount(LazyImage, {
            props: { src: 'https://example.com/photo.jpg' },
        });

        await wrapper.find('img').trigger('load');
        expect(wrapper.emitted('load')).toBeTruthy();
    });

    it('handles error and attempts original url fallback if thumbnail fails', async () => {
        const wrapper = mount(LazyImage, {
            props: { src: 'https://example.com/thumbnails/photo_thumb.jpg' },
        });

        const img = wrapper.find('img');
        // Initial src set to thumbnail
        expect(img.attributes('src')).toBe('https://example.com/thumbnails/photo_thumb.jpg');

        // Trigger error on thumbnail
        await img.trigger('error');
        // Fallback should replace _thumb. with . and /thumbnails/ with /
        expect(img.attributes('src')).toBe('https://example.com/photo.jpg');
    });

    it('cleans up observer on unmount', () => {
        const wrapper = mount(LazyImage, {
            props: { src: 'https://example.com/unmount.jpg' },
        });

        expect(() => wrapper.unmount()).not.toThrow();
    });
});
