<template>
  <div class="relative inline-block w-full">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed, provide, ref } from 'vue';
import { JANARI_SELECT_KEY } from './composables/selectContext';

const props = defineProps<{
  modelValue?: string;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const open = ref(false);
const placeholder = ref('');
const labels = ref<Record<string, string>>({});
const triggerRef = ref<HTMLElement | null>(null);

const modelValue = computed({
  get: () => props.modelValue ?? '',
  set: (value: string) => emit('update:modelValue', value),
});

const close = () => { open.value = false; };
const select = (value: string) => {
  modelValue.value = value;
  close();
};

provide(JANARI_SELECT_KEY, {
  open,
  modelValue,
  placeholder,
  labels,
  triggerRef,
  close,
  select,
});
</script>
