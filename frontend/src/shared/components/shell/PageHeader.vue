<template>
  <header
    class="mb-8 border-b border-border/50 pb-6"
    :class="borderless ? 'border-0 pb-0 mb-6' : ''"
  >
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div class="space-y-2 min-w-0">
        <p
          v-if="eyebrow"
          class="text-[10px] font-semibold uppercase tracking-[0.22em] text-primary"
        >
          {{ eyebrow }}
        </p>
        <h1
          class="font-bold tracking-tight text-foreground"
          :class="displaySize ? 'text-3xl' : 'text-2xl sm:text-3xl'"
        >
          <slot name="title">{{ title }}</slot>
        </h1>
        <p
          v-if="subtitle || $slots.subtitle"
          class="text-sm text-muted-foreground leading-relaxed max-w-2xl"
        >
          <slot name="subtitle">{{ subtitle }}</slot>
        </p>
      </div>
      <div
        v-if="$slots.actions"
        class="flex shrink-0 flex-wrap items-center gap-2"
      >
        <slot name="actions" />
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        title?: string;
        subtitle?: string;
        eyebrow?: string;
        displaySize?: boolean;
        borderless?: boolean;
    }>(),
    { title: undefined, subtitle: undefined, eyebrow: undefined, displaySize: false, borderless: false },
);
</script>
