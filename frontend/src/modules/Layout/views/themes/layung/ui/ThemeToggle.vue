<template>
  <button
    type="button"
    class="p-1.5 rounded-[var(--layung-radius-sm)] text-muted-foreground hover:text-foreground hover:bg-muted/80 transition-colors focus:outline-none focus:ring-2 focus:ring-sky-500/50"
    :aria-label="themeLabel"
    :title="themeLabel"
    @click="toggleMode"
  >
    <Sun
      v-if="isDark"
      class="w-4 h-4 text-amber-400"
    />
    <Moon
      v-else
      class="w-4 h-4 text-slate-700 dark:text-slate-300"
    />
  </button>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Sun, Moon } from 'lucide-vue-next';
import { useDarkMode } from '@/shared/composables/useDarkMode';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

const { isDark, toggleMode } = useDarkMode('frontend');
const { t } = useThemeI18n('layung');

const themeLabel = computed(() =>
  isDark.value
    ? t('header.themeAriaLight', 'Aktifkan mode terang')
    : t('header.themeAriaDark', 'Aktifkan mode gelap'),
);
</script>
