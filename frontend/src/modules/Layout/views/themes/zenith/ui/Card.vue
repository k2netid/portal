<template>
  <div
    :class="[
      'rounded-2xl border border-border/60 bg-card/80 backdrop-blur-md p-6 transition-all duration-300',
      hover ? 'hover:border-primary/40 hover:shadow-xl hover:shadow-primary/5 hover:-translate-y-1' : '',
      className
    ]"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    hover?: boolean;
    class?: string;
  }>(),
  {
    hover: false,
    class: '',
  }
);

const className = computed(() => props.class);
</script>
