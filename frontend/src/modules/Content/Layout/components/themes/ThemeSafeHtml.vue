<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import DOMPurify from 'dompurify';
import { DEFAULT_SANITIZE_CONFIG, CMS_SANITIZE_CONFIG } from '@/shared/utils/sanitizer';

defineOptions({
  inheritAttrs: false,
});

const props = defineProps<{
  html: string;
  tag?: string;
  mode?: 'default' | 'Jejakawan';
  config?: Record<string, unknown>;
}>();

const attrs = useAttrs();

/** Sanitized HTML for public theme views (host security utility, not console UI). */
const sanitizedHtml = computed(() => {
  if (!props.html) return '';

  const baseConfig = props.mode === 'Jejakawan' ? CMS_SANITIZE_CONFIG : DEFAULT_SANITIZE_CONFIG;
  const mergedConfig = { ...baseConfig, ...props.config };

  return DOMPurify.sanitize(props.html, mergedConfig);
});
</script>

<template>
  <!-- eslint-disable vue/no-v-html -->
  <component
    :is="tag || 'div'"
    v-bind="attrs"
    :class="['safe-html-content', attrs.class as string]"
    v-html="sanitizedHtml"
  />
  <!-- eslint-enable vue/no-v-html -->
</template>

<style scoped>
.safe-html-content {
  display: contents;
}
</style>
