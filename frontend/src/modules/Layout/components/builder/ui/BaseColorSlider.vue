<template>
  <div 
    class="color-slider" 
    :class="[`color-slider--${variant}`, { 'is-active': isActive }]"
  >
    <div class="slider-track" :style="trackStyle">
      <!-- Gradient overlay for alpha type -->
      <div v-if="variant === 'alpha'" class="slider-gradient" :style="gradientStyle"></div>
      
      <!-- Handle -->
      <div class="slider-handle" :style="{ left: handlePosition + '%' }"></div>
      
      <!-- Invisible input for interaction -->
      <input 
        type="range" 
        :min="min" 
        :max="max" 
        :step="step"
        :value="modelValue"
        @input="handleInput"
        @focus="isActive = true"
        @blur="isActive = false"
        class="slider-input"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import type { CSSProperties } from 'vue';

interface Props {
  modelValue?: number;
  min?: number;
  max?: number;
  step?: number;
  variant?: 'alpha' | 'hue' | 'saturation' | 'value';
  color?: string;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: 0,
  min: 0,
  max: 100,
  step: 1,
  variant: 'alpha',
  color: '#ffffff'
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: number): void;
}>();

const isActive = ref(false);

const handlePosition = computed(() => {
  const range = (props.max || 100) - (props.min || 0);
  return ((props.modelValue || 0) - (props.min || 0)) / range * 100;
});

const trackStyle = computed<CSSProperties>(() => {
  if (props.variant === 'hue') {
    return {
      background: 'linear-gradient(to right, #f00 0%, #ff0 17%, #0f0 33%, #0ff 50%, #00f 67%, #f0f 83%, #f00 100%)'
    };
  }
  if (props.variant === 'saturation') {
    return {
      background: 'linear-gradient(to right, #808080, #f00)'
    };
  }
  if (props.variant === 'value') {
    return {
      background: 'linear-gradient(to right, #000, #fff)'
    };
  }
  // Alpha variant uses checkerboard pattern (handled in CSS)
  return {};
});

const gradientStyle = computed<CSSProperties>(() => {
  if (props.variant === 'alpha') {
    return {
      background: `linear-gradient(to right, transparent, ${props.color})`
    };
  }
  return {};
});

const handleInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const value = parseFloat(target.value);
  emit('update:modelValue', value);
};
</script>

<style scoped>
.color-slider {
  position: relative;
  width: 100%;
  opacity: 0.7;
  transition: opacity 0.2s;
}

.color-slider:hover,
.color-slider.is-active {
  opacity: 1;
}

.slider-track {
  position: relative;
  width: 100%;
  height: 8px;
  border-radius: 4px;
  background: var(--builder-bg-tertiary, #374151);
  cursor: pointer;
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.2);
}

/* Alpha variant - checkerboard pattern */
.color-slider--alpha .slider-track {
  background-image: 
    linear-gradient(45deg, #444 25%, transparent 25%), 
    linear-gradient(-45deg, #444 25%, transparent 25%), 
    linear-gradient(45deg, transparent 75%, #444 75%), 
    linear-gradient(-45deg, transparent 75%, #444 75%);
  background-size: 8px 8px;
  background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
  background-color: var(--builder-bg-secondary);
}

/* Hue variant - rainbow gradient set via inline trackStyle */

.slider-gradient {
  position: absolute;
  inset: 0;
  border-radius: 4px;
  pointer-events: none;
}

.slider-handle {
  position: absolute;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: var(--builder-accent, #3b82f6);
  border: 2px solid #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,0.3), 0 0 0 1px rgba(0,0,0,0.1);
  pointer-events: none;
  z-index: 2;
  transition: transform 0.1s, box-shadow 0.2s;
}

.color-slider:hover .slider-handle,
.color-slider.is-active .slider-handle {
  transform: translate(-50%, -50%) scale(1.15);
  box-shadow: 0 2px 6px rgba(0,0,0,0.4), 0 0 0 3px rgba(59, 130, 246, 0.3);
}

.slider-input {
  position: absolute;
  inset: -8px 0;
  width: 100%;
  height: 24px;
  opacity: 0;
  cursor: pointer;
  z-index: 3;
  margin: 0;
}
</style>
