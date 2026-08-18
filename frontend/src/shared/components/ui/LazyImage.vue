<template>
  <img
    ref="imageRef"
    :src="placeholder || 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'1\' height=\'1\'%3E%3C/svg%3E'"
    :data-src="src"
    :alt="alt"
    :class="[
      'lazy-image',
      loadingClass,
      loadedClass,
      imageClass
    ]"
    @load="onLoad"
    @error="onError"
  >
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, type HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
    src: string;
    alt?: string;
    placeholder?: string | null;
    imageClass?: HTMLAttributes['class'];
    loadingClass?: HTMLAttributes['class'];
    loadedClass?: HTMLAttributes['class'];
    rootMargin?: string;
}>(), {
    alt: '',
    placeholder: null,
    imageClass: '',
    loadingClass: 'opacity-0',
    loadedClass: 'opacity-100',
    rootMargin: '50px',
});

const emit = defineEmits<{
    load: [];
    error: [event: Event];
}>();

const imageRef = ref<HTMLImageElement | null>(null);
const isLoaded = ref(false);
const hasError = ref(false);
let observer: IntersectionObserver | null = null;

const onLoad = () => {
    isLoaded.value = true;
    emit('load');
};

const onError = (event: Event) => {
    hasError.value = true;
    // Try to fallback to original URL if thumbnail fails
    const currentSrc = imageRef.value?.src;
    if (currentSrc && currentSrc.includes('_thumb.')) {
        // If thumbnail fails, try original URL
        const originalSrc = currentSrc.replace('_thumb.', '.').replace('/thumbnails/', '/');
        if (originalSrc !== currentSrc && imageRef.value) {
            imageRef.value.src = originalSrc;
            hasError.value = false; // Reset error to try original
            return;
        }
    }
    emit('error', event);
};

const loadImage = () => {
    if (imageRef.value && !isLoaded.value && !hasError.value) {
        const dataSrc = imageRef.value.getAttribute('data-src');
        if (dataSrc) {
            imageRef.value.src = dataSrc;
        }
    }
};

onMounted(() => {
    // For thumbnails, load immediately to avoid lazy loading issues
    const dataSrc = imageRef.value?.getAttribute('data-src');
    if (dataSrc && (dataSrc.includes('_thumb.') || dataSrc.includes('/thumbnails/'))) {
        // Load thumbnail immediately
        if (imageRef.value) {
            imageRef.value.src = dataSrc;
        }
        return;
    }
    
    // Check if IntersectionObserver is supported AND lazy loading is enabled via config
    const lazyEnabled = (window as unknown as { siteConfig?: { lazyLoading?: boolean } }).siteConfig?.lazyLoading ?? true;

    if ('IntersectionObserver' in window && lazyEnabled) {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        loadImage();
                        if (observer && imageRef.value) {
                            observer.unobserve(imageRef.value);
                        }
                    }
                });
            },
            {
                rootMargin: props.rootMargin,
            }
        );

        if (imageRef.value) {
            observer.observe(imageRef.value);
        }
    } else {
        // Fallback: load immediately if IntersectionObserver is not supported
        loadImage();
    }
});

onUnmounted(() => {
    if (observer && imageRef.value) {
        observer.unobserve(imageRef.value);
    }
});
</script>

<style scoped>
</style>

