<template>
  <iframe
    ref="frameRef"
    class="builder-public-preview-frame w-full min-h-[640px] border-0 bg-background"
    sandbox="allow-same-origin allow-scripts"
    title="Public page preview"
  />
</template>

<script setup lang="ts">
import { createApp, h, inject, onBeforeUnmount, onMounted, ref, watch, computed, type App } from 'vue';
import BlockRenderer from '@/modules/Layout/components/content-renderer/BlockRenderer.vue';
import i18n from '@/engine/i18n';
import { BUILDER_THEME_OVERRIDE_KEY } from '@/modules/Layout/composables/useTheme';
import type { BuilderInstance, BlockInstance } from '@/modules/Layout/types/builder';
import type { Theme } from '@/modules/Layout/types/theme';

const props = defineProps<{
  blocks: BlockInstance[];
}>();

const builder = inject<BuilderInstance | null>('builder', null);
const frameRef = ref<HTMLIFrameElement | null>(null);
let previewApp: App | null = null;

const copyStyles = (doc: Document): void => {
  document.querySelectorAll('link[rel="stylesheet"], style').forEach((node) => {
    doc.head.appendChild(node.cloneNode(true));
  });
};

const mountPreview = (): void => {
  const doc = frameRef.value?.contentDocument;
  if (!doc) {
    return;
  }

  previewApp?.unmount();
  previewApp = null;

  if (!doc.getElementById('builder-preview-root')) {
    doc.body.innerHTML = '<div id="builder-preview-root" class="min-h-full"></div>';
  }
  if (!doc.head.querySelector('[data-builder-preview-styles]')) {
    copyStyles(doc);
    const mark = doc.createElement('meta');
    mark.setAttribute('data-builder-preview-styles', '1');
    doc.head.appendChild(mark);
  }

  const root = doc.getElementById('builder-preview-root');
  if (!root) {
    return;
  }

  previewApp = createApp({
    render: () => h(BlockRenderer, { blocks: props.blocks, mode: 'view' }),
  });
  previewApp.use(i18n);
  if (builder?.themeData && builder?.themeSettings) {
    previewApp.provide(BUILDER_THEME_OVERRIDE_KEY, {
      activeTheme: computed(() => (builder.themeData.value as Theme | null) || null),
      themeSettings: builder.themeSettings,
    });
  }
  previewApp.mount(root);
};

onMounted(() => {
  const frame = frameRef.value;
  if (!frame) {
    return;
  }
  frame.addEventListener('load', mountPreview);
  frame.srcdoc = '<!doctype html><html><head><meta charset="utf-8"></head><body></body></html>';
});

watch(
  () => props.blocks,
  () => {
    mountPreview();
  },
  { deep: true },
);

onBeforeUnmount(() => {
  previewApp?.unmount();
  previewApp = null;
});
</script>
