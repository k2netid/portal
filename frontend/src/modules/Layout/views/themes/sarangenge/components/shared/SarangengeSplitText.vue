<template>
  <span
    class="sarangenge-split-text inline-block"
    :aria-label="text"
  >
    <span
      v-for="(word, wIdx) in words"
      :key="wIdx"
      class="inline-block whitespace-nowrap mr-[0.25em]"
    >
      <span
        v-for="(char, cIdx) in word.split('')"
        :key="cIdx"
        class="inline-block sarangenge-rise"
        :style="{ animationDelay: `${(wIdx * 4 + cIdx) * 0.03 + (delay || 0)}s` }"
      >
        {{ char }}
      </span>
    </span>
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    text: string;
    delay?: number;
  }>(),
  {
    delay: 0,
  }
);

const words = computed(() => {
  return (props.text || '').split(/\s+/).filter(Boolean);
});
</script>
