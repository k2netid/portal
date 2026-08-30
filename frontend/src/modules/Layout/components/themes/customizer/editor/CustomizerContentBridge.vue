<template>
  <aside
    class="rounded-xl border border-primary/20 bg-primary/5 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4"
    role="note"
  >
    <div class="flex-1 min-w-0 space-y-1">
      <p class="text-sm font-semibold text-foreground tracking-tight">
        {{ title }}
      </p>
      <p class="text-xs text-muted-foreground leading-relaxed">
        {{ body }}
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-2 shrink-0">
      <slot />
    </div>
  </aside>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  body: string;
}>();
</script>
