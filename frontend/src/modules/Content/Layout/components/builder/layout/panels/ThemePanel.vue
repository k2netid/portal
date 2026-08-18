<template>
  <div class="theme-panel">
    <div v-if="loading" class="theme-loading">
      <div class="spinner"></div>
      <span>{{ t('builder.panels.theme.loading') }}</span>
    </div>

    <template v-else-if="activeTheme">
      <div class="theme-info">
        <h4 class="theme-name">{{ activeTheme.name }}</h4>
        <p class="theme-description">{{ activeTheme.description }}</p>
      </div>

      <div class="theme-settings">
        <!-- Theme Setting Sections -->
        <div v-for="section in settingsSections" :key="section.id" class="settings-section">
          <button 
            class="section-header" 
            @click="toggleSection(section.id)"
          >
            <span class="section-title">{{ section.label }}</span>
            <component :is="expandedSections.includes(section.id) ? ChevronDown : ChevronRight" :size="16" />
          </button>

          <div v-if="expandedSections.includes(section.id)" class="section-content">
            <div v-for="setting in section.settings" :key="setting.key" class="setting-item">
              <label class="setting-label">{{ setting.label }}</label>
              
              <!-- Color Field -->
              <div v-if="setting.type === 'color'" class="setting-field color-field">
                <input 
                  type="color" 
                  v-model="formValues[setting.key]"
                  @input="handleInput(setting.key)"
                />
                <input 
                  type="text" 
                  v-model="formValues[setting.key]"
                  class="color-text-input"
                  @change="handleInput(setting.key)"
                />
              </div>

              <!-- Select Field -->
              <select 
                v-else-if="setting.type === 'select'"
                v-model="formValues[setting.key]"
                class="setting-field select-field"
                @change="handleInput(setting.key)"
              >
                <option v-for="opt in setting.options" :key="String(opt.value)" :value="opt.value">
                  {{ opt.label }}
                </option>
              </select>

              <!-- Number Field -->
              <input 
                v-else-if="setting.type === 'number'"
                type="number"
                v-model.number="formValues[setting.key]"
                class="setting-field number-field"
                :min="setting.min"
                :max="setting.max"
                :step="setting.step"
                @input="handleInput(setting.key)"
              />

              <!-- Default Text/Fallback -->
              <input 
                v-else
                type="text"
                v-model="formValues[setting.key]"
                class="setting-field text-field"
                @input="handleInput(setting.key)"
              />
              
              <p v-if="setting.description" class="setting-description">{{ setting.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="empty-state">
      {{ t('builder.panels.theme.noActive') }}
    </div>

    <div class="panel-footer" v-if="isDirty">
      <button class="save-theme-btn" :disabled="saving" @click="saveSettings">
        {{ saving ? t('builder.common.saving') : t('builder.common.save') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, inject, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import ChevronRight from 'lucide-vue-next/dist/esm/icons/chevron-right.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import type { BuilderInstance } from '@/types/builder';
import type { ThemeSetting } from '@/types/theme';

interface SettingItem extends ThemeSetting {
  key: string;
}

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const activeTheme = computed(() => builder?.themeData?.value);
const loading = computed(() => builder?.loadingThemes?.value);
const saving = ref(false);
const isDirty = ref(false);
const formValues = ref<Record<string, unknown>>({});
const expandedSections = ref<string[]>(['General', 'Colors']);

const settingsSections = computed(() => {
  if (!activeTheme.value?.manifest?.settings_schema) return [];
  
  const schema = activeTheme.value.manifest.settings_schema;
  const sectionsMap: Record<string, { id: string; label: string; settings: SettingItem[] }> = {};

  Object.keys(schema).forEach(key => {
    const setting = schema[key];
    const category = setting.category || 'General';
    
    if (!sectionsMap[category]) {
      sectionsMap[category] = {
        id: category,
        label: category,
        settings: [],
      };
    }
    
    sectionsMap[category].settings.push({ key, ...setting });
  });

  return Object.values(sectionsMap);
});

const loadSettings = () => {
    if (!activeTheme.value) return;
    
    const defaults: Record<string, unknown> = {};
    const schema = activeTheme.value.manifest?.settings_schema || {};
    
    Object.keys(schema).forEach(key => {
        defaults[key] = schema[key].default ?? '';
    });

    formValues.value = { ...defaults, ...(activeTheme.value.settings || {}) };
    isDirty.value = false;
};

const handleInput = (_key: string) => {
    isDirty.value = true;
    // Live update builder theme settings for preview
    if (builder?.themeSettings) {
        builder.themeSettings.value = { ...formValues.value };
        // Potentially call applyThemeStyles if useTheme is available in builder
        if (builder.applyThemeStyles) {
            builder.applyThemeStyles();
        }
    }
};

const toggleSection = (id: string) => {
    const idx = expandedSections.value.indexOf(id);
    if (idx > -1) {
        expandedSections.value.splice(idx, 1);
    } else {
        expandedSections.value.push(id);
    }
};

const saveSettings = async () => {
  if (!isDirty.value || !activeTheme.value || !builder) return;
  
  saving.value = true;
  try {
    await builder.updateThemeSettings(activeTheme.value.slug, formValues.value);
    isDirty.value = false;
  } catch (error) {
    logger.error('Failed to save theme settings:', error);
  } finally {
    saving.value = false;
  }
};

watch(activeTheme, () => {
    loadSettings();
}, { immediate: true });

onMounted(() => {
    if (!activeTheme.value) {
        builder?.loadTheme(null);
    } else {
        loadSettings();
    }
});
</script>

<style scoped>
.theme-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.theme-info {
  padding: 16px;
  border-bottom: 1px solid var(--builder-border);
  background: var(--builder-bg-secondary);
}

.theme-name {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--builder-text-primary);
}

.theme-description {
  margin: 4px 0 0;
  font-size: 11px;
  color: var(--builder-text-muted);
}

.theme-settings {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;
}

.settings-section {
  border-bottom: 1px solid var(--builder-border);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 12px 16px;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}

.section-header:hover {
  background: var(--builder-bg-secondary);
}

.section-title {
  font-size: 12px;
  font-weight: 600;
  color: var(--builder-text-primary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.section-content {
  padding: 8px 16px 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.setting-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.setting-label {
  font-size: 11px;
  font-weight: 500;
  color: var(--builder-text-secondary);
}

.setting-field {
  width: 100%;
  height: 32px;
  padding: 0 8px;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  color: var(--builder-text-primary);
  font-size: 12px;
  outline: none;
}

.setting-field:focus {
  border-color: var(--builder-accent);
}

.color-field {
  display: flex;
  gap: 8px;
  height: auto;
  padding: 0;
  background: transparent;
  border: none;
}

.color-field input[type="color"] {
  width: 32px;
  height: 32px;
  padding: 0;
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  cursor: pointer;
  background: none;
}

.color-text-input {
  flex: 1;
  height: 32px;
  padding: 0 8px;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  color: var(--builder-text-primary);
  font-size: 12px;
}

.setting-description {
  margin: 0;
  font-size: 10px;
  color: var(--builder-text-muted);
}

.panel-footer {
  padding: 16px;
  border-top: 1px solid var(--builder-border);
  background: var(--builder-bg-primary);
}

.save-theme-btn {
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

.save-theme-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.theme-loading, .empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px;
  color: var(--builder-text-muted);
  text-align: center;
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
