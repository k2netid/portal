<template>
  <div class="base-slider-input">
    <BaseSlider
      :model-value="Number(modelValue || 0)"
      :min="min"
      :max="max"
      :step="step"
      @update:model-value="(val) => $emit('update:modelValue', val)"
      class="slider-part"
    />
    <div class="input-part">
      <input
        type="number"
        class="numeric-input"
        :class="{ 'is-placeholder-active': (modelValue === 0 || modelValue === '') && placeholderValue !== null }"
        :value="modelValue"
        :placeholder="String(placeholderValue ?? 0)"
        :min="min"
        :max="max"
        @input="handleInput"
      />
      <span v-if="unit" class="unit-label">{{ unit }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import BaseSlider from './BaseSlider.vue';

interface Props {
  modelValue?: number | string;
  placeholderValue?: number | null;
  min?: number;
  max?: number;
  step?: number;
  unit?: string;
}

withDefaults(defineProps<Props>(), {
  modelValue: 0,
  placeholderValue: null,
  min: 0,
  max: 100,
  step: 1,
  unit: ''
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: number): void;
}>();

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const val = target.value;
  emit('update:modelValue', val === '' ? 0 : Number(val));
};
</script>

<style scoped>
.base-slider-input {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  width: 100%;
}

.slider-part {
  flex: 1;
}

.input-part {
  display: flex;
  align-items: center;
  gap: 4px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
  padding: 0 4px;
  height: 28px;
}

.numeric-input {
  width: 40px;
  background: transparent;
  border: none;
  color: var(--builder-text-primary);
  font-size: 12px;
  text-align: center;
  outline: none;
  padding: 0;
}

.numeric-input.is-placeholder-active::placeholder {
  color: var(--builder-text-muted);
  opacity: 0.7;
}

/* Hide number arrows */
.numeric-input::-webkit-outer-spin-button,
.numeric-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.unit-label {
  font-size: 10px;
  color: var(--builder-text-muted);
  user-select: none;
}
</style>
