<template>
  <div 
    class="flex items-center gap-3 cursor-pointer select-none group py-1"
    @click="toggle"
    :class="{ 'opacity-50 pointer-events-none': disabled }"
  >
    <Switch 
      :checked="modelValue" 
      :disabled="disabled"
      class="pointer-events-none"
    />
    <Label v-if="label" class="cursor-pointer font-medium text-[13px] text-slate-700 dark:text-slate-300 group-hover:text-primary transition-colors">
      {{ label }}
    </Label>
  </div>
</template>

<script setup lang="ts">
import { Switch, Label } from '@/components/ui';

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

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void;
  (e: 'change', value: boolean): void;
}>();

const toggle = () => {
  if (props.disabled) return;
  const newValue = !props.modelValue;
  emit('update:modelValue', newValue);
  emit('change', newValue);
};
</script>

