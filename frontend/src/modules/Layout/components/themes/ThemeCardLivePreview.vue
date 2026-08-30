<template>
  <div
    ref="containerRef"
    class="relative h-full w-full overflow-hidden bg-muted"
  >
    <div
      class="absolute left-0 top-0 origin-top-left"
      :style="scaledWrapperStyle"
    >
      <iframe
        :src="src"
        :title="title"
        class="pointer-events-none block border-0"
        :style="iframeStyle"
        loading="lazy"
        tabindex="-1"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = withDefaults(
  defineProps<{
    src?: string;
    title?: string;
    /** Desktop viewport baseline for thumbnail scaling */
    viewportWidth?: number;
    viewportHeight?: number;
  }>(),
  {
    src: '/',
    title: 'Theme live preview',
    viewportWidth: 1280,
    viewportHeight: 800,
  },
);

const containerRef = ref<HTMLElement | null>(null);
const containerWidth = ref(0);
const containerHeight = ref(0);

const scale = computed(() => {
  if (!containerWidth.value || !containerHeight.value) {
    return 1;
  }
  return Math.min(
    containerWidth.value / props.viewportWidth,
    containerHeight.value / props.viewportHeight,
  );
});

const scaledWrapperStyle = computed(() => ({
  width: `${props.viewportWidth}px`,
  height: `${props.viewportHeight}px`,
  transform: `scale(${scale.value})`,
}));

const iframeStyle = computed(() => ({
  width: `${props.viewportWidth}px`,
  height: `${props.viewportHeight}px`,
}));

let resizeObserver: ResizeObserver | null = null;

const updateContainerSize = () => {
  const el = containerRef.value;
  if (!el) return;
  containerWidth.value = el.clientWidth;
  containerHeight.value = el.clientHeight;
};

onMounted(() => {
  updateContainerSize();
  const el = containerRef.value;
  if (!el || typeof ResizeObserver === 'undefined') return;
  resizeObserver = new ResizeObserver(() => updateContainerSize());
  resizeObserver.observe(el);
});

onUnmounted(() => {
  resizeObserver?.disconnect();
});
</script>
