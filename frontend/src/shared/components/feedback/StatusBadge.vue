<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
    :class="toneClass"
  >
    {{ label }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        tone?: 'default' | 'primary' | 'success' | 'warning' | 'destructive';
    }>(),
    { tone: 'default' },
);

const toneClass = computed(() => {
    if (props.tone === 'primary') return 'bg-primary/15 text-primary';
    if (props.tone === 'success') return 'bg-success/10 text-success';
    if (props.tone === 'warning') return 'bg-warning/10 text-warning';
    if (props.tone === 'destructive') return 'bg-destructive/10 text-destructive';
    return 'bg-muted text-muted-foreground';
});
</script>
