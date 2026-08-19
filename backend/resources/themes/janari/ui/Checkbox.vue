<template>
  <button
    type="button"
    role="checkbox"
    :aria-checked="checkedState"
    :class="cn(
      'peer h-4 w-4 shrink-0 rounded border border-input bg-background transition-colors',
      'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20',
      'disabled:cursor-not-allowed disabled:opacity-50',
      isChecked ? 'bg-primary border-primary text-primary-foreground' : '',
      props.class,
    )"
    :disabled="disabled"
    @click="toggle"
  >
    <svg
      v-if="isChecked"
      class="h-3.5 w-3.5 mx-auto"
      viewBox="0 0 15 15"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M11.4669 3.72684C11.7558 3.91574 11.8369 4.30308 11.648 4.59198L7.39799 11.092C7.29783 11.2452 7.13556 11.3467 6.95402 11.3699C6.77247 11.3931 6.58989 11.3355 6.45446 11.2124L3.70446 8.71241C3.44905 8.48022 3.43023 8.08494 3.66242 7.82953C3.89461 7.57412 4.28989 7.55529 4.5453 7.78749L6.75292 9.79441L10.6018 3.90708C10.7907 3.61818 11.178 3.53705 11.4669 3.72684Z"
        fill="currentColor"
        fill-rule="evenodd"
        clip-rule="evenodd"
      />
    </svg>
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = withDefaults(defineProps<{
  id?: string;
  checked?: boolean | 'indeterminate';
  disabled?: boolean;
  class?: HTMLAttributes['class'];
}>(), {
  checked: false,
  disabled: false,
  id: undefined,
  class: undefined,
});

const emit = defineEmits<{
  (e: 'update:checked', value: boolean | 'indeterminate'): void;
}>();

const isChecked = computed(() => props.checked === true);
const checkedState = computed(() => (props.checked === 'indeterminate' ? 'mixed' : isChecked.value));

const toggle = () => {
  if (props.disabled) return;
  emit('update:checked', !isChecked.value);
};
</script>
