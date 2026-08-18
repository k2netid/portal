<template>
  <div class="theme-settings-panel">
    <div v-if="loading" class="theme-loading">
      <div class="spinner"></div>
      <span>{{ t('builder.panels.theme.loading') }}</span>
    </div>

    <template v-else-if="currentTheme">
      <div class="theme-header">
        <h3 class="theme-title">{{ currentTheme.name }} {{ t('builder.panels.theme.settings') }}</h3>
      </div>

      <div class="theme-settings-body">
        <div v-for="section in settingsSections" :key="section.id" class="settings-section">
          <div class="section-label">{{ section.label }}</div>
          
          <div class="section-content">
            <div v-for="setting in section.settings" :key="setting.key" class="setting-item">
              <label class="setting-label">{{ setting.label }}</label>
              
              <!-- Field Rendering Logic -->
              <div v-if="setting.type === 'color'" class="color-field-container">
                <ColorField 
                  :field="{ name: setting.key, type: 'color', label: setting.label || setting.key }"
                  :value="String(formValues[setting.key] || '')"
                  @update:value="formValues[setting.key] = $event; handleInput()"
                  :placeholder-value="setting.default as string"
                />
              </div>

              <div v-else-if="setting.type === 'media' || setting.type === 'upload'" class="media-field-container">
                <UploadField 
                  :field="{ name: setting.key, type: 'upload', label: setting.label || setting.key }"
                  :value="String(formValues[setting.key] || '')"
                  @update:value="formValues[setting.key] = $event; handleInput()"
                  :placeholder-value="setting.default as string"
                />
              </div>

              <select v-else-if="setting.type === 'select'" v-model="formValues[setting.key]" class="select-input" @change="handleInput">
                <option v-for="opt in setting.options" :key="String(opt.value)" :value="opt.value">{{ opt.label }}</option>
              </select>

              <div v-else-if="setting.type === 'checkbox'" class="checkbox-setting">
                <input type="checkbox" v-model="formValues[setting.key]" @change="handleInput" />
                <span>{{ setting.description || '' }}</span>
              </div>

              <div v-else-if="setting.type === 'range'" class="range-setting">
                <input type="range" v-model.number="formValues[setting.key]" :min="setting.min" :max="setting.max" :step="setting.step" @input="handleInput" />
                <span class="range-value">{{ formValues[setting.key] }}{{ (setting as any).unit || 'px' }}</span>
              </div>

              <textarea v-else-if="setting.type === 'textarea'" v-model="formValues[setting.key]" class="textarea-input" rows="3" @input="handleInput"></textarea>

              <input v-else-if="setting.type === 'number'" type="number" v-model.number="formValues[setting.key]" class="number-input" @input="handleInput" />
              
              <input v-else type="text" v-model="formValues[setting.key]" class="text-input" @input="handleInput" />

              <p v-if="setting.description" class="setting-hint">{{ setting.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="panel-footer" v-if="isDirty">
        <button class="save-btn" :disabled="saving" @click="saveSettings">
          {{ saving ? t('builder.common.saving') : t('builder.common.save') }}
        </button>
      </div>
    </template>

    <div v-else class="empty-state">
      <Palette :size="48" />
      <p>{{ t('builder.panels.theme.selectHint') }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/utils/logger';
import { ref, computed, inject, watch, onMounted, defineAsyncComponent } from 'vue';
import { useI18n } from 'vue-i18n';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import type { BuilderInstance, ThemeData } from '@/types/builder';
import type { ThemeSetting } from '@/types/theme';

interface SettingItem extends ThemeSetting {
  key: string;
}

const ColorField = defineAsyncComponent(() => import('@/components/builder/fields/ColorField.vue'));
const UploadField = defineAsyncComponent(() => import('@/components/builder/fields/UploadField.vue'));

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const loading = ref(false);
const saving = ref(false);
const isDirty = ref(false);
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const formValues = ref<Record<string, any>>({});

const selectedThemeSlug = computed(() => builder?.selectedThemeSlug?.value);
const themes = computed(() => builder?.availableThemes?.value || []);

const currentTheme = computed(() => {
  return themes.value.find((t: ThemeData) => t.slug === selectedThemeSlug.value);
});

const settingsSections = computed(() => {
  if (!currentTheme.value?.manifest?.settings_schema) return [];
  
  const schema = currentTheme.value.manifest.settings_schema;
  const sections: Record<string, { id: string; label: string; settings: SettingItem[] }> = {};

  Object.keys(schema).forEach(key => {
    const setting = schema[key];
    const category = setting.category || 'General';
    if (!sections[category]) {
      sections[category] = { id: category, label: category, settings: [] };
    }
    sections[category].settings.push({ key, ...setting });
  });

  return Object.values(sections);
});

const loadThemeSettings = () => {
    if (!currentTheme.value) return;
    const theme = currentTheme.value;
    const schema = theme.manifest?.settings_schema || {};
    const defaults: Record<string, unknown> = {};
    
    Object.keys(schema).forEach(key => {
        defaults[key] = schema[key].default ?? '';
    });
    
    formValues.value = { ...defaults, ...(theme.settings || {}) };
    isDirty.value = false;
};

const handleInput = () => {
  isDirty.value = true;
  // Real-time preview if this is the active theme
  if (selectedThemeSlug.value === builder?.activeTheme?.value && builder?.themeSettings) {
    builder.themeSettings.value = { ...formValues.value };
    if (builder.applyThemeStyles) {
        builder.applyThemeStyles();
    }
  }
};

const saveSettings = async () => {
  if (!selectedThemeSlug.value || !builder) return;
  saving.value = true;
  try {
    await builder.updateThemeSettings(selectedThemeSlug.value, formValues.value);
    isDirty.value = false;
  } catch (error) {
    logger.error('Failed to save theme settings:', error);
  } finally {
    saving.value = false;
  }
};

watch(selectedThemeSlug, () => {
    loadThemeSettings();
});

onMounted(() => {
    loadThemeSettings();
});
</script>

<style scoped>
.theme-settings-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: var(--builder-bg-primary);
}

.theme-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--builder-border);
}

.theme-title {
  margin: 0;
  font-size: 14px;
  font-weight: 700;
  color: var(--builder-text-primary);
}

.theme-settings-body {
  flex: 1;
  overflow-y: auto;
  padding: 12px 0;
}

.settings-section {
  margin-bottom: 8px;
}

.section-label {
  padding: 8px 20px;
  font-size: 11px;
  font-weight: 700;
  color: var(--builder-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: var(--builder-bg-secondary);
}

.section-content {
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.setting-item {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.setting-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--builder-text-secondary);
}

.text-input, .number-input, .select-input, .textarea-input {
  width: 100%;
  height: 32px;
  padding: 0 8px;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  color: var(--builder-text-primary);
  font-size: 12px;
}

.textarea-input {
  height: auto;
  padding: 8px;
  resize: vertical;
}

.checkbox-setting {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--builder-text-secondary);
}

.range-setting {
    display: flex;
    align-items: center;
    gap: 12px;
}

.range-setting input[type="range"] {
    flex: 1;
}

.range-value {
    min-width: 40px;
    font-size: 11px;
    font-weight: 600;
    color: var(--builder-text-primary);
}

.color-field-container, .media-field-container {
    width: 100%;
}

.setting-hint {
  margin: 0;
  font-size: 11px;
  color: var(--builder-text-muted);
  font-style: italic;
}

.panel-footer {
  padding: 16px 20px;
  border-top: 1px solid var(--builder-border);
}

.save-btn {
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

.save-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--builder-text-muted);
  padding: 40px;
  text-align: center;
}

.theme-loading {
  padding: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: var(--builder-text-muted);
}

.spinner {
  width: 24px;
  height: 24px;
  border: 2px solid var(--builder-border);
  border-top-color: var(--builder-accent);
  border-radius: 50%;
  animation: rotate 0.8s linear infinite;
}

@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
