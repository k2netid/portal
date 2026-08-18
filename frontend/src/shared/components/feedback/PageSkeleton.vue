<template>
  <div class="space-y-6 animate-pulse" role="status" :aria-label="label">
    <div class="space-y-3">
      <div class="h-8 w-48 max-w-full rounded-lg bg-muted/60" />
      <div class="h-4 w-72 max-w-full rounded-md bg-muted/40" />
    </div>
    <div
      v-if="statCount > 0"
      class="grid gap-4 sm:grid-cols-2"
      :class="statGridClass"
    >
      <div
        v-for="i in statCount"
        :key="i"
        class="h-[104px] rounded-xl bg-muted/30 border border-border/30"
      />
    </div>
    <div
      v-if="showPanel"
      class="h-48 rounded-xl bg-muted/25 border border-border/30"
    />
    <span class="sr-only">{{ label }}</span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label?: string;
        statCount?: number;
        showPanel?: boolean;
    }>(),
    { label: 'Loading', statCount: 4, showPanel: true },
);

const statGridClass = computed(() => {
    if (props.statCount >= 4) return 'lg:grid-cols-4';
    if (props.statCount === 3) return 'lg:grid-cols-3';
    return 'lg:grid-cols-2';
});
</script>
