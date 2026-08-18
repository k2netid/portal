<template>
  <div class="space-y-6">
    <PageHeader
      v-if="showHeader"
      :title="title"
      :subtitle="subtitle"
      :borderless="borderless"
    >
      <template v-if="$slots.actions" #actions>
        <slot name="actions" />
      </template>
    </PageHeader>
    <slot />
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';

withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
        showHeader?: boolean;
        borderless?: boolean;
    }>(),
    { subtitle: undefined, showHeader: true, borderless: false },
);
</script>
