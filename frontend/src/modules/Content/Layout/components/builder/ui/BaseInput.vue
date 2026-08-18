<template>
  <div class="relative flex items-center w-full group">
    <div v-if="$slots.prefix || prefix" class="absolute left-3 text-muted-foreground flex items-center">
      <slot name="prefix">{{ prefix }}</slot>
    </div>

    <Textarea
      v-if="type === 'textarea'"
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      v-bind="$attrs"
      :class="cn(
        'min-h-[80px] w-full',
        (prefix || $slots.prefix) && 'pl-10',
        (suffix || $slots.suffix) && 'pr-10',
        error && 'border-destructive focus-visible:ring-destructive'
      )"
    />
    
    <Input
      v-else
      :type="type"
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      v-bind="$attrs"
      :class="cn(
        'w-full',
        (prefix || $slots.prefix) && 'pl-10',
        (suffix || $slots.suffix) && 'pr-10',
        error && 'border-destructive focus-visible:ring-destructive'
      )"
    />

    <div v-if="$slots.suffix || suffix" class="absolute right-3 text-muted-foreground flex items-center">
        <slot name="suffix">{{ suffix }}</slot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Input, Textarea } from '@/components/ui';

interface Props {
  modelValue?: string | number;
  type?: string;
  placeholder?: string;
  disabled?: boolean;
  prefix?: string;
  suffix?: string;
  error?: boolean;
}

withDefaults(defineProps<Props>(), {
  modelValue: '',
  type: 'text',
  placeholder: '',
  disabled: false,
  prefix: '',
  suffix: '',
  error: false
});

defineEmits<{
  (e: 'update:modelValue', value: string | number): void;
  (e: 'blur', event: FocusEvent): void;
  (e: 'focus', event: FocusEvent): void;
}>();
</script>
