<template>
  <div class="flex items-center w-full py-2">
    <input
      type="range"
      class="w-full h-1.5 bg-slate-200 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-primary hover:accent-primary/80 transition-colors"
      :value="modelValue"
      :min="min"
      :max="max"
      :step="step"
      @input="handleInput"
    />
  </div>
</template>

<script setup lang="ts">
interface Props {
  modelValue?: number;
  min?: number;
  max?: number;
  step?: number;
}

withDefaults(defineProps<Props>(), {
  modelValue: 0,
  min: 0,
  max: 100,
  step: 1
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: number): void;
}>();

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', Number(target.value));
};
</script>
