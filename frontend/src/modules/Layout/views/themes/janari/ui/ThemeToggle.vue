<template>
  <button
    type="button"
    :class="cn(
      'relative flex items-center justify-center w-9 h-9 rounded-full',
      'text-muted-foreground hover:text-foreground',
      'hover:bg-primary/5 hover:ring-4 hover:ring-primary/10',
      'active:scale-95 transition-all duration-300 focus:outline-none',
      props.class,
    )"
    :aria-label="isDark ? t('theme.janari.header.themeAriaLight') : t('theme.janari.header.themeAriaDark')"
    :title="isDark ? t('theme.janari.header.themeAriaLight') : t('theme.janari.header.themeAriaDark')"
    @click="toggleMode"
  >
    <Sun
      class="h-[1.15rem] w-[1.15rem] transition-all duration-300"
      :class="{ 'rotate-0 scale-100': !isDark, '-rotate-90 scale-0': isDark }"
      stroke-width="1.5"
    />
    <Moon
      class="absolute h-[1.15rem] w-[1.15rem] transition-all duration-300"
      :class="{ 'rotate-90 scale-0': !isDark, 'rotate-0 scale-100': isDark }"
      stroke-width="1.5"
    />
  </button>
</template>

<script setup lang="ts">
import { Sun, Moon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useDarkMode } from '@/shared/composables/useDarkMode';
import { cn } from './utils/classNames';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
  class?: HTMLAttributes['class'];
}>();

const { t } = useI18n();
const { toggleMode, isDark } = useDarkMode('frontend');
</script>
