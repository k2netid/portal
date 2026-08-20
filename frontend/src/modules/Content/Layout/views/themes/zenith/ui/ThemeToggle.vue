<template>
  <button
    type="button"
    aria-label="Toggle Theme"
    class="w-9 h-9 rounded-full flex items-center justify-center border border-border/60 bg-background/60 text-foreground hover:bg-muted transition-colors"
    @click="toggleDark"
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
import { useDarkMode } from '@/shared/composables/useDarkMode';
import { Sun, Moon } from 'lucide-vue-next';

const { isDark, toggleDark } = useDarkMode('frontend');
</script>
