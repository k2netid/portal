<script setup lang="ts">
import { computed, useAttrs } from 'vue';
import DOMPurify from 'dompurify';
import type { Config } from 'dompurify';
import { DEFAULT_SANITIZE_CONFIG, CMS_SANITIZE_CONFIG } from '@/shared/utils/sanitizer';

defineOptions({
  inheritAttrs: false
});

const props = defineProps<{
  html: string;
  tag?: string;
  mode?: 'default' | 'cms' | 'publishing' | 'Jejakawan';
  config?: Config;
}>();

const attrs = useAttrs();

/**
 * SafeHtml component renders sanitized HTML content using DOMPurify.
 * This is the approved replacement for direct v-html usage across the platform.
 */
const sanitizedHtml = computed(() => {
  if (!props.html) return '';
  
  const baseConfig = (props.mode === 'cms' || props.mode === 'publishing' || props.mode === 'Jejakawan') ? CMS_SANITIZE_CONFIG : DEFAULT_SANITIZE_CONFIG;
  const mergedConfig = { ...baseConfig, ...props.config };
  
  return DOMPurify.sanitize(props.html, mergedConfig);
});
</script>

<template>
  <!-- eslint-disable vue/no-v-html -->
  <component 
    :is="tag || 'div'" 
    v-html="sanitizedHtml" 
    v-bind="attrs"
    :class="['safe-html-content', (attrs.class as string)]"
  />
  <!-- eslint-enable vue/no-v-html -->
</template>

<style scoped>
.safe-html-content {
  display: contents;
}
</style>
