<template>
  <button
    type="button"
    class="p-2 rounded-[var(--layung-radius-sm)] text-muted-foreground hover:text-foreground hover:bg-muted/80 transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500/50"
    :aria-label="isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'"
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
import { Sun, Moon } from 'lucide-vue-next';
import { useDarkMode } from '@/shared/composables/useDarkMode';

const { isDark, toggleMode } = useDarkMode('frontend');
</script>
