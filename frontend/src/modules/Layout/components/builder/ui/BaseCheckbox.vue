<template>
  <div class="flex items-center space-x-2 group cursor-pointer" @click="toggle">
    <Checkbox 
      :id="id" 
      :checked="modelValue" 
      @update:checked="$emit('update:modelValue', $event)"
      :disabled="disabled"
      class="transition-[background-color,border-color] duration-200"
    />
    <Label 
      v-if="label" 
      :for="id"
      class="text-[13px] font-medium leading-none cursor-pointer group-hover:text-primary transition-colors"
    >
      {{ label }}
    </Label>
  </div>
</template>

<script setup lang="ts">
import { Checkbox, Label } from '@/components/ui';

interface Props {
  modelValue?: boolean;
  label?: string;
  disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: false,
  label: '',
  disabled: false
});

const id = `checkbox-${Math.random().toString(36).substring(2, 9)}`;

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
}>();

const toggle = () => {
    if (props.disabled) return;
    emit('update:modelValue', !props.modelValue);
};
</script>
