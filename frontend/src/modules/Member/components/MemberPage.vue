<template>
  <div class="member-page space-y-6 min-w-0 max-w-full">
    <PageHeader
      borderless
      :title="title"
      :subtitle="subtitle"
      :eyebrow="eyebrow"
    >
      <template
        v-if="$slots.actions"
        #actions
      >
        <slot name="actions" />
      </template>
      <template
        v-if="$slots.subtitle"
        #subtitle
      >
        <slot name="subtitle" />
      </template>
    </PageHeader>
    <slot />
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';

withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
        eyebrow?: string;
    }>(),
    { title: undefined, subtitle: undefined, eyebrow: undefined },
);
</script>
