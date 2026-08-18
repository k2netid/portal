<template>
  <div class="preferences-panel">
    <!-- Interface Settings -->
    <div class="pref-section">
      <h4 class="section-title">{{ t('builder.panels.preferences.interface') }}</h4>
      
      <!-- Language Switcher -->
      <div class="pref-item">
        <span class="pref-label">{{ t('builder.toolbar.switchLanguage') }}</span>
        <BaseDropdown align="right" width="auto">
          <template #trigger>
            <button class="pref-value-btn">
              {{ currentLocaleName }}
              <ChevronDown :size="14" />
            </button>
          </template>
          
          <template #default="{ close }">
            <button 
              v-for="loc in availableLocales" 
              :key="loc.code"
              class="dropdown-item"
              :class="{ 'active': currentLocale === loc.code }"
              @click="changeLanguage(loc.code); close()"
            >
              {{ loc.name }}
            </button>
          </template>
        </BaseDropdown>
      </div>

      <div class="pref-item">
        <span class="pref-label">{{ t('builder.panels.preferences.darkMode') }}</span>
        <BaseToggle 
          v-model="darkMode" 
        />
      </div>
    </div>

    <!-- Editor Settings -->
    <div class="pref-section">
      <h4 class="section-title">{{ t('builder.panels.preferences.editor') }}</h4>
      
      <div class="pref-item">
        <span class="pref-label">{{ t('builder.panels.preferences.showGrid') }}</span>
        <BaseToggle 
          v-model="showGrid" 
        />
      </div>

      <div class="pref-item">
        <span class="pref-label">{{ t('builder.panels.preferences.snapToObjects') }}</span>
        <BaseToggle 
          v-model="snapToObjects" 
        />
      </div>
    </div>

    <!-- System Settings -->
    <div class="pref-section">
      <h4 class="section-title">{{ t('builder.panels.preferences.system') }}</h4>
      
      <div class="pref-item">
        <span class="pref-label">{{ t('builder.panels.preferences.autoSave') }}</span>
        <BaseToggle 
          v-model="autoSave" 
        />
      </div>
    </div>

    <!-- About -->
    <div class="pref-footer">
      <span class="version-text">{{ t('builder.panels.preferences.version', { version: '1.0.0' }) }}</span>
      <span class="build-text">{{ t('builder.panels.preferences.build', { date: '2026.01.11' }) }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { BaseToggle, BaseDropdown } from '@/components/builder/ui';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import { useLanguage } from '@/composables/useLanguage';
import { useCmsStore } from '@/stores/cms';
import type { BuilderInstance } from '@/types/builder';

const { t } = useI18n();
const cmsStore = useCmsStore();
const darkMode = computed({
  get: () => cmsStore.isDarkMode,
  set: (val: boolean) => cmsStore.toggleDarkMode(val)
});
const builder = inject<BuilderInstance>('builder');

// Use builder preferences (persisted to localStorage)
const showGrid = computed({
    get: () => builder?.showGrid?.value ?? false,
    set: (val: boolean) => { if (builder?.showGrid) builder.showGrid.value = val; }
});
const snapToObjects = computed({
    get: () => builder?.snapToObjects?.value ?? true,
    set: (val: boolean) => { if (builder?.snapToObjects) builder.snapToObjects.value = val; }
});
const autoSave = computed({
    get: () => builder?.autoSave?.value ?? true,
    set: (val: boolean) => { if (builder?.autoSave) builder.autoSave.value = val; }
});

// Language Logic via useLanguage composable for global sync
const { 
    languages: availableLocales, 
    currentLanguageCode: currentLocale, 
    setLanguage: changeLanguage 
} = useLanguage();

const currentLocaleName = computed(() => {
    return availableLocales.value.find(l => l.code === currentLocale.value)?.name || currentLocale.value;
});
</script>

<style scoped>
.preferences-panel {
  padding: var(--spacing-md);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xl);
}

.section-title {
  font-size: var(--font-size-xs);
  letter-spacing: 0.5px;
  color: var(--builder-text-muted);
  margin: 0 0 var(--spacing-sm) 0;
  font-weight: 600;
  text-transform: uppercase;
}

.pref-section {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.pref-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-sm);
}

.pref-label {
  font-size: var(--font-size-sm);
  color: var(--builder-text-primary);
}

.pref-value-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: none;
  color: var(--builder-text-secondary);
  font-size: var(--font-size-sm);
  cursor: pointer;
  padding: 0;
}

.pref-value-btn:hover {
  color: var(--builder-text-primary);
}

.dropdown-item {
  padding: 6px 12px;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  color: var(--builder-text-secondary);
  cursor: pointer;
  border-radius: 4px;
}

.dropdown-item:hover {
  background: var(--builder-bg-tertiary);
  color: var(--builder-text-primary);
}

.dropdown-item.active {
  background: var(--builder-accent);
  color: white;
}

.pref-footer {
  margin-top: auto;
  padding-top: var(--spacing-lg);
  border-top: 1px solid var(--builder-border);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.version-text, .build-text {
  font-size: 10px;
  color: var(--builder-text-muted);
}
</style>
