<template>
  <div
    class="relative flex items-center w-full select-none touch-none group h-6 cursor-pointer"
    :class="[disabled ? 'opacity-50 cursor-not-allowed' : '', $attrs.class]"
  >
    <!-- Visual Track -->
    <div class="relative h-2 w-full grow overflow-hidden rounded-full bg-slate-200 dark:bg-zinc-800 border border-border/80">
      <!-- Filled Progress Bar -->
      <div
        class="h-full bg-primary rounded-full transition-all duration-75"
        :style="{ width: `${percentage}%` }"
      />
    </div>

    <!-- Visual Thumb -->
    <div
      class="pointer-events-none absolute top-1/2 -translate-y-1/2 h-5 w-5 rounded-full border-2 border-background bg-primary shadow-md transition-transform duration-75 group-hover:scale-110 group-active:scale-95 peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-background"
      :style="{ left: `calc(${percentage}% - ${(percentage / 100) * 20}px)` }"
    />

    <!-- Transparent Native Input for full accessibility and seamless interaction -->
    <input
      :id="id"
      type="range"
      :min="min"
      :max="max"
      :step="step"
      :value="modelValue"
      :aria-label="ariaLabel"
      :disabled="disabled"
      class="peer absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed z-10 m-0 p-0"
      @input="onInput"
    >
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

defineOptions({
  inheritAttrs: false,
});

const props = withDefaults(
  defineProps<{
    modelValue?: number | string;
    min?: number | string;
    max?: number | string;
    step?: number | string;
    id?: string;
    ariaLabel?: string;
    disabled?: boolean;
  }>(),
  {
    modelValue: 0,
    min: 0,
    max: 100,
    step: 1,
    disabled: false,
  }
);

const emit = defineEmits<{
  (e: 'update:modelValue', value: number): void;
}>();

const percentage = computed(() => {
  const min = Number(props.min) || 0;
  const max = Number(props.max) ?? 100;
  if (max <= min) return 0;
  const val = Number(props.modelValue) || 0;
  const clamped = Math.min(Math.max(val, min), max);
  return ((clamped - min) / (max - min)) * 100;
});

const onInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', Number(target.value));
};
</script>
