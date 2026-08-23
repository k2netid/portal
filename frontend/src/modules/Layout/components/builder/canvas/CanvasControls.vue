<template>
  <div class="canvas-controls" :class="{ 'canvas-controls--mobile': isMobile }">
      <!-- Actions -->
      <div class="canvas-actions">
        <!-- Undo/Redo -->
        <IconButton 
          :icon="Undo2" 
          :disabled="!canUndo" 
          @click="builder?.undo()" 
          :title="$t('builder.toolbar.undo')"
          class="canvas-btn"
        />
        <IconButton 
          :icon="Redo2" 
          :disabled="!canRedo" 
          @click="builder?.redo()" 
          :title="$t('builder.toolbar.redo')"
          class="canvas-btn"
        />

        <BaseDivider orientation="vertical" :margin="4" />

        <!-- Theme Switcher -->
        <BaseDropdown align="center" width="200px">
          <template #trigger="{ open }">
            <button class="theme-btn" :class="{ 'theme-btn--active': open }" :title="$t('builder.toolbar.theme')">
              <Palette :size="14" />
              <span class="theme-name">{{ currentThemeName }}</span>
              <ChevronDown :size="12" class="ml-auto" />
            </button>
          </template>
          
          <template #default="{ close }">
            <div class="dropdown-header">{{ $t('builder.toolbar.switchTheme', 'Switch Theme') }}</div>
            <div v-if="loadingThemes" class="dropdown-loading">
                <Loader2 :size="16" class="animate-spin" />
            </div>
            <template v-else>
               <button 
                v-for="theme in availableThemes" 
                :key="theme.slug"
                class="dropdown-item"
                :class="{ 'active': activeThemeSlug === theme.slug }"
                @click="changeTheme(theme.slug); close()"
              >
                <div class="flex items-center justify-between w-full">
                  <span>{{ theme.name }}</span>
                  <Check v-if="activeThemeSlug === theme.slug" :size="14" />
                </div>
              </button>
            </template>
          </template>
        </BaseDropdown>
      </div>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, ref, onMounted, onUnmounted } from 'vue';
import Undo2 from 'lucide-vue-next/dist/esm/icons/undo-2.js';
import Redo2 from 'lucide-vue-next/dist/esm/icons/redo-2.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import Loader2 from 'lucide-vue-next/dist/esm/icons/loader-circle.js';
import { IconButton, BaseDivider, BaseDropdown } from '../ui';
import type { BuilderInstance } from '@/types/builder';

// Props & Emits
defineEmits<{
  (e: 'save'): void;
}>();

// Inject Builder
const builder = inject<BuilderInstance>('builder');
const isMobile = ref(false);

// State
const canUndo = computed(() => builder?.canUndo?.value || false);
const canRedo = computed(() => builder?.canRedo?.value || false);
const activeThemeSlug = computed(() => builder?.activeTheme?.value || 'janari');
const availableThemes = computed(() => builder?.availableThemes?.value || []);
const loadingThemes = computed(() => builder?.loadingThemes?.value || false);

const currentThemeName = computed(() => {
    const theme = availableThemes.value.find(t => t.slug === activeThemeSlug.value);
    return theme ? theme.name : activeThemeSlug.value;
});

const changeTheme = (slug: string) => {
    builder?.loadTheme(slug);
};

import { throttle } from '@/shared/utils/performance';

// ... (existing imports)

// Window resize listener for responsive class
const checkMobile = () => {
    isMobile.value = window.innerWidth <= 768;
};

const throttledCheck = throttle(checkMobile, 200);

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', throttledCheck);
    
    if (builder && availableThemes.value.length === 0) {
        builder.fetchThemes();
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', throttledCheck);
});
</script>

<style scoped>
.canvas-controls {
  /* Position in the gutter area above the canvas */
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px 0;
  z-index: 10;
}

.canvas-actions {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px;
    background: var(--builder-bg-secondary, #242830);
    border: 1px solid var(--builder-border, #4a5568);
    border-radius: var(--border-radius-md, 8px);
}

.canvas-btn {
    width: 32px;
    height: 32px;
}

.theme-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 10px;
    height: 32px;
    min-width: 120px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 4px;
    color: var(--builder-text-secondary);
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.theme-btn:hover {
    background: var(--builder-bg-tertiary, rgba(255,255,255,0.05));
    color: var(--builder-text-primary);
}

.theme-btn--active {
    background: var(--builder-bg-tertiary, rgba(255,255,255,0.05));
    border-color: var(--builder-border);
}

.theme-name {
    max-width: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dropdown-header {
    padding: 8px 12px;
    font-size: 10px;
    text-transform: uppercase;
    color: var(--builder-text-muted);
    font-weight: 700;
    letter-spacing: 0.5px;
}

.dropdown-loading {
    padding: 20px;
    display: flex;
    justify-content: center;
    color: var(--builder-text-muted);
}

.dropdown-item {
  padding: 8px 12px;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  color: var(--builder-text-secondary);
  cursor: pointer;
  border-radius: 4px;
  font-size: 13px;
  display: flex;
  align-items: center;
}

.dropdown-item:hover {
  background: var(--builder-bg-tertiary, rgba(255,255,255,0.05));
  color: var(--builder-text-primary);
}

.dropdown-item.active {
  background: var(--builder-accent);
  color: white;
}

/* Dark mode overrides (default for builder) */
.ja-builder--dark .canvas-actions {
    background: #2d323b;
    border-color: #4a5568;
}

/* Light mode overrides */
.ja-builder--light .canvas-actions {
    background: #f8fafc;
    border-color: #e2e8f0;
}
.ja-builder--light .canvas-btn,
.ja-builder--light .theme-btn {
    color: #374151;
}

/* On mobile, span full width */
.canvas-controls--mobile {
    justify-content: center;
    padding: 6px 16px;
}
</style>
