<template>
  <button
    type="button"
    class="flex flex-col gap-2 rounded-xl border p-4 text-left transition-all hover:bg-muted/10"
    :class="active
      ? 'border-primary bg-primary/5 ring-1 ring-primary/30'
      : 'border-border/50 bg-card'"
    @click="emit('select')"
  >
    <slot name="preview" />
    <span class="text-xs font-semibold text-foreground">{{ label }}</span>
    <span class="text-xs text-foreground/80">{{ description }}</span>
  </button>
</template>

<script setup lang="ts">
defineProps<{
    label: string;
    description: string;
    active?: boolean;
}>();

const emit = defineEmits<{
    select: [];
}>();
</script>
