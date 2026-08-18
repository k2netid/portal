<template>
  <button
    class="relative flex items-center justify-center w-9 h-9 rounded-full text-muted-foreground hover:text-foreground hover:bg-primary/5 hover:ring-4 hover:ring-primary/10 hover:scale-110 active:scale-95 transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background"
    @click="toggleMode"
  >
    <Sun 
      class="h-[1.2rem] w-[1.2rem] transition-all duration-300" 
      :class="{'rotate-0 scale-100': !isDark, '-rotate-90 scale-0': isDark}"
      stroke-width="1.5" 
    />
    <Moon 
      class="absolute h-[1.2rem] w-[1.2rem] transition-all duration-300" 
      :class="{'rotate-90 scale-0': !isDark, 'rotate-0 scale-100': isDark}"
      stroke-width="1.5" 
    />
    <span class="sr-only">{{ t('common.labels.accessibility.toggleTheme') }}</span>
  </button>
</template>

<script setup lang="ts">
import {
  Moon,
  Sun,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { useDarkMode, type DarkModeScope } from '@/shared/composables/useDarkMode';

const props = withDefaults(defineProps<{
  scope?: DarkModeScope;
}>(), {
  scope: 'console',
});

const { t } = useI18n();
const { toggleMode, isDark } = useDarkMode(props.scope);
</script>
