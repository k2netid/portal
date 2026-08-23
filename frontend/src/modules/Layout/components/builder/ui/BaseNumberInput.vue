<template>
  <div class="relative flex items-center w-full group overflow-hidden border border-input rounded-md bg-background focus-within:ring-1 focus-within:ring-primary h-9">
    <!-- Prefix Label (e.g., TL, TR) -->
    <div v-if="label" class="pl-2 pr-1 text-[10px] font-bold text-muted-foreground uppercase select-none border-r border-input py-1 bg-muted/30">
        {{ label }}
    </div>

    <!-- Input -->
    <input
      type="number"
      :value="modelValue"
      @input="handleInput"
      @keydown.up.prevent="increment"
      @keydown.down.prevent="decrement"
      v-bind="$attrs"
      class="w-full bg-transparent px-2 text-sm outline-none text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
    />

    <!-- Custom Spinners -->
    <div class="flex flex-col border-l border-input h-full">
      <button 
        type="button"
        @click="increment"
        class="flex-1 px-1 hover:bg-muted transition-colors border-b border-input flex items-center justify-center text-muted-foreground hover:text-foreground"
      >
        <ChevronUp :size="10" />
      </button>
      <button 
        type="button"
        @click="decrement"
        class="flex-1 px-1 hover:bg-muted transition-colors flex items-center justify-center text-muted-foreground hover:text-foreground"
      >
        <ChevronDown :size="10" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import ChevronUp from 'lucide-vue-next/dist/esm/icons/chevron-up.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';

interface Props {
  modelValue: number | string;
  label?: string;
  step?: number;
  min?: number;
  max?: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: number | string): void;
}>();

const handleInput = (e: Event) => {
    const val = (e.target as HTMLInputElement).value;
    emit('update:modelValue', val === '' ? '' : Number(val));
};

const increment = () => {
    let val = Number(props.modelValue || 0);
    val += props.step || 1;
    if (props.max !== undefined && val > props.max) val = props.max;
    emit('update:modelValue', val);
};

const decrement = () => {
    let val = Number(props.modelValue || 0);
    val -= props.step || 1;
    if (props.min !== undefined && val < props.min) val = props.min;
    emit('update:modelValue', val);
};
</script>

<style scoped>
/* Ensure no native spinners */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
</style>
