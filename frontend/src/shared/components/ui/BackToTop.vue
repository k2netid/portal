<template>
  <Transition
    enter-active-class="transition-[opacity,transform] duration-200"
    enter-from-class="opacity-0 translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-[opacity,transform] duration-200"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-2"
  >
    <button
      v-if="show"
      :class="[
        'absolute z-50 w-9 h-9 bg-primary text-primary-foreground rounded-full shadow-lg hover:shadow-xl transition-transform hover:scale-110 flex items-center justify-center group',
        positionClass
      ]"
      :title="backToTopLabel"
      :aria-label="backToTopLabel"
      @click="$emit('click')"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="transition-transform group-hover:-translate-y-0.5"
      >
        <path d="m18 15-6-6-6 6" />
      </svg>
    </button>
  </Transition>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import type { HTMLAttributes } from 'vue';

const { t } = useI18n({ useScope: 'global' })
const backToTopLabel = computed(() => t('layout.frontend.backToTop'))

withDefaults(defineProps<{
    show: boolean;
    positionClass?: HTMLAttributes['class'];
}>(), {
    positionClass: 'bottom-6 left-1/2 -translate-x-1/2',
});

defineEmits<{
    click: [];
}>();
</script>
