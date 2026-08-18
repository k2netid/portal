<template>
  <div class="theme-list-panel">
    <div class="panel-header-actions">
      <BaseInput 
        v-model="searchQuery"
        :placeholder="t('builder.panels.theme.searchPlaceholder')"
        class="search-input"
      >
        <template #prefix>
          <Search :size="14" />
        </template>
      </BaseInput>
    </div>

    <div v-if="loading" class="theme-loading">
      <div class="spinner"></div>
      <span>{{ t('builder.panels.theme.loading') }}</span>
    </div>

    <div v-else class="theme-grid">
      <div 
        v-for="theme in filteredThemes" 
        :key="theme.slug"
        class="theme-card"
        :class="{ 
            'theme-card--active': activeTheme === theme.slug,
            'theme-card--selected': selectedThemeSlug === theme.slug 
        }"
        @click="selectTheme(theme.slug)"
      >
        <div class="theme-preview">
          <img v-if="theme.preview && typeof theme.preview === 'string'" :src="theme.preview" :alt="theme.name" />
          <div v-else class="preview-placeholder">
            <Palette :size="32" />
          </div>
          <div v-if="activeTheme === theme.slug" class="active-badge">
            <Check :size="12" />
            <span>Active</span>
          </div>
        </div>
        <div class="theme-info">
          <h4 class="theme-name">{{ theme.name }}</h4>
          <p class="theme-version">v{{ theme.version || '1.0.0' }}</p>
        </div>
      </div>
    </div>

    <div class="panel-footer" v-if="selectedThemeSlug && selectedThemeSlug !== activeTheme">
      <button class="activate-theme-btn" @click="activateTheme">
        {{ t('builder.panels.theme.activate') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, inject, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import { BaseInput } from '@/components/builder/ui';
import type { BuilderInstance, ThemeData } from '@/types/builder';

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const searchQuery = ref('');
const loading = computed(() => builder?.loadingThemes?.value);
const activeTheme = computed(() => builder?.activeTheme?.value);
const selectedThemeSlug = computed(() => builder?.selectedThemeSlug?.value);
const themes = computed(() => builder?.availableThemes?.value || []);

const filteredThemes = computed(() => {
  if (!searchQuery.value) return themes.value;
  const query = searchQuery.value.toLowerCase();
  return themes.value.filter((t: ThemeData) => 
    t.name.toLowerCase().includes(query) || 
    t.slug.toLowerCase().includes(query)
  );
});

const selectTheme = (slug: string) => {
  if (builder?.selectedThemeSlug) {
      builder.selectedThemeSlug.value = slug;
  }
};

const activateTheme = async () => {
    if (!selectedThemeSlug.value || !builder) return;
    try {
        await builder.loadTheme(selectedThemeSlug.value);
    } catch (error) {
        logger.error('Failed to activate theme:', error);
    }
};

onMounted(() => {
    if (themes.value.length === 0) {
        builder?.fetchThemes();
    }
    // Default select active theme
    if (!selectedThemeSlug.value && activeTheme.value && builder?.selectedThemeSlug) {
        builder.selectedThemeSlug.value = activeTheme.value;
    }
});
</script>

<style scoped>
.theme-list-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.panel-header-actions {
  padding: 16px;
  background: var(--builder-bg-primary);
  border-bottom: 1px solid var(--builder-border);
}

.theme-grid {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}

.theme-card {
  border: 1px solid var(--builder-border);
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s;
  background: var(--builder-bg-secondary);
}

.theme-card:hover {
  border-color: var(--builder-accent);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.theme-card--selected {
  border-color: var(--builder-accent);
  box-shadow: 0 0 0 2px var(--builder-accent);
}

.theme-preview {
  aspect-ratio: 16/10;
  background: var(--builder-bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.theme-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-placeholder {
  color: var(--builder-text-muted);
}

.active-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: var(--status-success);
  color: white;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 4px;
}

.theme-info {
  padding: 12px;
}

.theme-name {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: var(--builder-text-primary);
}

.theme-version {
  margin: 2px 0 0;
  font-size: 11px;
  color: var(--builder-text-muted);
}

.panel-footer {
  padding: 16px;
  border-top: 1px solid var(--builder-border);
  background: var(--builder-bg-primary);
}

.activate-theme-btn {
  width: 100%;
  padding: 8px;
  background: var(--builder-accent);
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.theme-loading {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--builder-text-muted);
}

.spinner {
  width: 24px;
  height: 24px;
  border: 2px solid var(--builder-border);
  border-top-color: var(--builder-accent);
  border-radius: 50%;
  animation: rotate 0.8s linear infinite;
  margin-bottom: 12px;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
