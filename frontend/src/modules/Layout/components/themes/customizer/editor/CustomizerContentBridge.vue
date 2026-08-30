<template>
  <aside
    class="rounded-xl border border-primary/20 bg-primary/5 p-3.5 flex flex-col gap-3 min-w-0 overflow-hidden"
    role="note"
  >
    <div class="min-w-0 space-y-1">
      <p class="text-sm font-semibold text-foreground tracking-tight break-words">
        {{ title }}
      </p>
      <p class="text-xs text-muted-foreground leading-relaxed break-words">
        {{ body }}
      </p>
    </div>
    <div class="flex flex-col gap-2 min-w-0 w-full">
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
