<template>
  <span :class="cn('truncate', !displayText && 'text-muted-foreground')">
    {{ displayText || placeholder }}
  </span>
</template>

<script setup lang="ts">
import { computed, inject, watch } from 'vue';
import { JANARI_SELECT_KEY } from './composables/selectContext';
import { cn } from './utils/classNames';

const props = defineProps<{
  placeholder?: string;
}>();

const ctx = inject(JANARI_SELECT_KEY);
if (!ctx) throw new Error('SelectValue must be used inside Select');

watch(() => props.placeholder, (value) => {
  if (value !== undefined) ctx.placeholder.value = value;
}, { immediate: true });

const displayText = computed(() => {
  const value = ctx.modelValue.value;
  if (!value) return '';
  return ctx.labels.value[value] ?? value;
});

const placeholder = computed(() => ctx.placeholder.value || props.placeholder || '');
</script>
