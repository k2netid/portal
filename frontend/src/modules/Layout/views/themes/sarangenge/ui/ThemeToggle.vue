<template>
  <button
    type="button"
    :aria-label="themeLabel"
    :title="themeLabel"
    class="w-9 h-9 rounded-full flex items-center justify-center border border-border/60 bg-background/60 text-foreground hover:bg-muted transition-colors"
    @click="toggleMode"
  >
    <Sun
      v-if="isDark"
      class="w-4 h-4 text-amber-400 animate-in spin-in-180 duration-300"
    />
    <Moon
      v-else
      class="w-4 h-4 text-muted-foreground animate-in spin-in-180 duration-300"
    />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useDarkMode } from '@/shared/composables/useDarkMode';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';
import { Sun, Moon } from 'lucide-vue-next';

const { isDark, toggleMode } = useDarkMode('frontend');
const { t } = useThemeI18n('sarangenge');

const themeLabel = computed(() =>
  isDark.value
    ? t('header.themeAriaLight', 'Aktifkan mode terang')
    : t('header.themeAriaDark', 'Aktifkan mode gelap'),
);
</script>
