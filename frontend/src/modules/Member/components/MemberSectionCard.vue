<template>
  <section class="rounded-xl border border-border/60 bg-card shadow-sm">
    <div
      v-if="title"
      class="border-b border-border/50 px-5 py-3.5 sm:px-6"
    >
      <h2 class="text-xs font-semibold uppercase tracking-[0.14em] text-muted-foreground">
        {{ title }}
      </h2>
    </div>
    <div class="p-5 sm:p-6 space-y-4">
      <slot />
    </div>
  </section>
</template>

<script setup lang="ts">
defineProps<{
  title?: string;
}>();
</script>
