<template>
  <div class="rounded-lg border border-dashed border-border p-4 text-sm text-muted-foreground">
    <p class="font-medium text-foreground">Media module not installed</p>
    <p class="mt-1">Activate the Media pack to pick images. Paste a URL or media id for now.</p>
    <input
      class="mt-3 w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
      :value="modelValue"
      :placeholder="placeholder || 'https://… or media id'"
      @input="onInput"
    >
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue?: string | number | null;
  placeholder?: string;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

function onInput(event: Event) {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
}

void props;
</script>
