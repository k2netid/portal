<script setup lang="ts">
import { Card } from '@/shared/components/ui';
</script>

<template>
  <Card data-console-glass class="overflow-hidden rounded-xl border border-border/50 bg-card shadow-none min-w-0 max-w-full">
    <div
      v-if="$slots.toolbar"
      class="flex flex-col gap-4 border-b border-border/50 p-4 lg:flex-row lg:items-center lg:justify-between"
    >
      <slot name="toolbar" />
    </div>
    <div :class="[$slots.toolbar ? 'border-t border-border/40' : '', 'min-w-0 max-w-full']">
      <slot />
    </div>
    <div
      v-if="$slots.footer"
      class="border-t border-border/50 px-4 py-3 sm:px-6"
    >
      <slot name="footer" />
    </div>
  </Card>
</template>
