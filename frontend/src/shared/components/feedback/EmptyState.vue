<template>
  <div
    class="flex flex-col items-center justify-center py-12 px-4 text-center"
    :class="compact ? 'py-8' : ''"
  >
    <div
      v-if="icon"
      class="flex h-14 w-14 items-center justify-center rounded-2xl bg-muted/40 text-muted-foreground mb-4"
    >
      <component :is="icon" class="h-7 w-7 opacity-60" />
    </div>
    <p class="font-semibold text-foreground">{{ title }}</p>
    <p
      v-if="description"
      class="max-w-sm text-sm text-muted-foreground mt-2 leading-relaxed"
    >
      {{ description }}
    </p>
    <div v-if="$slots.action" class="mt-5">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        icon?: Component;
        compact?: boolean;
    }>(),
    { description: undefined, icon: undefined, compact: false },
);
</script>
