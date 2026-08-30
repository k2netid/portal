<template>
  <div class="rounded-lg border border-dashed border-border p-6 text-sm text-muted-foreground">
    <p class="font-medium text-foreground">Layout / Visual Builder not installed</p>
    <p class="mt-1">
      Install the Layout pack for JA-Builder. Until then, edit content with the text editor.
    </p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue?: unknown;
}>();
defineEmits<{
  (e: 'update:modelValue', value: unknown): void;
  (e: 'close'): void;
}>();
</script>
