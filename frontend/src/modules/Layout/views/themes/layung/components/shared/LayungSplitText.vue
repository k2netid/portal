<template>
  <span
    class="layung-split-text inline-block"
    :aria-label="text"
  >
    <span
      v-for="(unit, idx) in units"
      :key="idx"
      class="motion-split-unit inline-block overflow-hidden"
    >
      <span
        class="motion-split-inner inline-block"
        :data-unit="unit"
      >
        {{ unit }}<span v-if="isWordMode && idx < units.length - 1">&nbsp;</span>
      </span>
    </span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    text: string;
    type?: 'words' | 'chars';
  }>(),
  { type: 'words' },
);

const isWordMode = computed(() => props.type !== 'chars');

const units = computed(() => {
  if (!props.text) return [];
  if (props.type === 'chars') return props.text.split('');
  return props.text.trim().split(/\s+/).filter(Boolean);
});
</script>
