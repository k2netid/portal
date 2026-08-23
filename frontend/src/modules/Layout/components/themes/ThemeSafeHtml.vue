<template>
  <div
    class="theme-safe-html-root"
    v-html="sanitizedContent"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { sanitizeCmsHtml } from '@/shared/utils/sanitizer';

const props = withDefaults(
  defineProps<{
    html?: string | null;
  }>(),
  {
    html: '',
  }
);

const sanitizedContent = computed(() => {
  return sanitizeCmsHtml(props.html || '');
});
</script>
