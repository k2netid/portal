<template>
  <div class="relative w-full">
    <select
      :value="modelValue"
      :disabled="disabled"
      :required="required"
      :class="[
        'w-full appearance-none px-4 py-2.5 pr-10 rounded-[var(--sarangenge-radius-sm,0.85rem)] border border-border/80 bg-background/80 text-foreground transition-all duration-200 focus:outline-none focus:border-[var(--sarangenge-teal,#0f766e)] focus:ring-2 focus:ring-[var(--sarangenge-teal,#0f766e)]/20 disabled:opacity-50 disabled:cursor-not-allowed text-sm shadow-sm cursor-pointer',
        className
      ]"
      v-bind="$attrs"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <slot />
    </select>
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-muted-foreground">
      <ChevronDown class="w-4 h-4" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

const props = withDefaults(
  defineProps<{
    modelValue?: string | number;
    disabled?: boolean;
    required?: boolean;
    class?: string;
  }>(),
  {
    modelValue: '',
    disabled: false,
    required: false,
    class: '',
  }
);

defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const className = computed(() => props.class);
</script>
