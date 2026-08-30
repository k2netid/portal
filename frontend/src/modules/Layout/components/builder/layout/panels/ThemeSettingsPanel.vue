<template>
  <div class="theme-settings-panel">
    <div v-if="loading" class="theme-loading">
      <div class="spinner"></div>
      <span>{{ t('builder.panels.theme.loading') }}</span>
    </div>

    <template v-else-if="currentTheme">
      <div class="theme-header">
        <h3 class="theme-title">{{ currentTheme.name }} {{ t('builder.panels.theme.settings') }}</h3>
        <p class="theme-subtitle">
          {{ t('builder.panels.theme.schemaHint', 'Same settings schema as Theme Customizer (platform + theme).') }}
        </p>
      </div>

      <div class="theme-settings-body">
        <div v-for="section in settingsSections" :key="section.id" class="settings-section">
          <div class="section-label">{{ section.label }}</div>

          <div class="section-content">
            <div v-for="setting in section.settings" :key="setting.key" class="setting-item">
              <SettingControl
                :setting="setting"
                :model-value="formValues[setting.key]"
                :theme-slug="currentTheme?.slug"
                @update:model-value="(val) => { formValues[setting.key] = val; handleInput() }"
                @change="handleInput"
                @pick-media="openMediaPicker(setting.key)"
              />
            </div>
          </div>
        </div>

        <div v-if="settingsSections.length === 0" class="empty-schema">
          {{ t('builder.panels.theme.noSettings', 'No editable theme settings for this theme.') }}
        </div>
      </div>

      <div class="panel-footer actions">
        <button
          v-if="isDirty"
          class="save-btn"
          :disabled="saving"
          @click="saveSettings"
        >
          {{ saving ? t('builder.common.saving') : t('builder.common.save') }}
        </button>
        <RouterLink
          v-if="currentTheme?.slug"
          class="link-btn"
          :to="{ name: 'themes.customizer', params: { slug: currentTheme.slug } }"
        >
          {{ t('builder.panels.theme.openCustomizer', 'Open Theme Customizer') }}
        </RouterLink>
        <RouterLink class="link-btn" :to="{ name: 'menus' }">
          {{ t('builder.panels.theme.openMenus', 'Edit Menus') }}
        </RouterLink>
      </div>
    </template>

    <div v-else class="empty-state">
      <Palette :size="48" />
      <p>{{ t('builder.panels.theme.selectHint') }}</p>
    </div>

    <MediaPicker
      v-model:open="showMediaPicker"
      @selected="handleMediaSelect"
    >
      <template #trigger>
        <span class="hidden" />
      </template>
    </MediaPicker>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, inject, watch, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import { useI18n } from 'vue-i18n';
import Palette from 'lucide-vue-next/dist/esm/icons/palette.js';
import type { BuilderInstance, ThemeData } from '@/modules/Layout/types/builder';
import type { ThemeSetting } from '@/modules/Layout/types/theme';
import { mergeThemeSettingsSchema } from '@/modules/Layout/customizer/loaders/mergeThemeSettingsSchema';
import SettingControl from '@/modules/Layout/components/themes/customizer/sidebar/SettingControl.vue';
import MediaPicker from '@/modules/Media/components/picker/MediaPicker.vue';

interface SettingItem extends ThemeSetting {
  key: string;
}

const { t } = useI18n();
const builder = inject<BuilderInstance>('builder');

const loading = ref(false);
const saving = ref(false);
const isDirty = ref(false);
const formValues = ref<Record<string, unknown>>({});
const showMediaPicker = ref(false);
const activeMediaKey = ref<string | null>(null);

const selectedThemeSlug = computed(() => builder?.selectedThemeSlug?.value);
const themes = computed(() => builder?.availableThemes?.value || []);

const currentTheme = computed(() => {
  return themes.value.find((t: ThemeData) => t.slug === selectedThemeSlug.value)
    || (builder?.themeData.value?.slug === selectedThemeSlug.value ? builder.themeData.value : null);
});

/** Platform + theme customizer schema (+ API legacy keys), same as Theme Customizer. */
const mergedSchema = computed((): Record<string, ThemeSetting> => {
  const theme = currentTheme.value;
  const slug = theme?.slug || selectedThemeSlug.value;
  if (!slug) return {};
  return mergeThemeSettingsSchema(
    slug,
    (theme?.manifest?.settings_schema || null) as Record<string, unknown> | null,
  ) as Record<string, ThemeSetting>;
});

const settingsSections = computed(() => {
  const schema = mergedSchema.value;
  const sections: Record<string, { id: string; label: string; settings: SettingItem[] }> = {};

  Object.keys(schema).forEach((key) => {
    const setting = schema[key];
    if (!setting || setting.hidden) return;
    // Menu slots + bindings live in Customizer dedicated panels.
    if (key.startsWith('menu_location_') || key.startsWith('_')) return;

    const category = setting.category || 'General';
    if (!sections[category]) {
      sections[category] = { id: category, label: category, settings: [] };
    }
    sections[category]!.settings.push({ key, ...setting });
  });

  return Object.values(sections);
});

const loadThemeSettings = () => {
  if (!currentTheme.value && !selectedThemeSlug.value) return;
  const theme = currentTheme.value;
  const schema = mergedSchema.value;
  const defaults: Record<string, unknown> = {};

  Object.keys(schema).forEach((key) => {
    const item = schema[key];
    if (!item || item.hidden) return;
    defaults[key] = item.default ?? '';
  });

  const liveSettings = selectedThemeSlug.value === builder?.activeTheme?.value
    ? (builder?.themeSettings.value || {})
    : {};

  formValues.value = {
    ...defaults,
    ...(theme?.settings || {}),
    ...liveSettings,
  };
  isDirty.value = false;
};

const handleInput = () => {
  isDirty.value = true;
  // Live-update builder canvas only (scoped CSS via Canvas.injectThemeStyles) — no document :root.
  if (selectedThemeSlug.value === builder?.activeTheme?.value && builder?.themeSettings) {
    builder.themeSettings.value = { ...formValues.value };
  }
};

const openMediaPicker = (key: string) => {
  activeMediaKey.value = key;
  showMediaPicker.value = true;
};

const handleMediaSelect = (m: { url: string }) => {
  if (activeMediaKey.value) {
    formValues.value[activeMediaKey.value] = m.url;
    handleInput();
  }
  showMediaPicker.value = false;
  activeMediaKey.value = null;
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

watch([selectedThemeSlug, mergedSchema], () => {
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

.theme-subtitle {
  margin: 6px 0 0;
  font-size: 11px;
  line-height: 1.4;
  color: var(--builder-text-muted);
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

.empty-schema {
  padding: 24px 20px;
  font-size: 12px;
  color: var(--builder-text-muted);
  text-align: center;
}

.panel-footer {
  padding: 16px 20px;
  border-top: 1px solid var(--builder-border);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.panel-footer.actions {
  gap: 10px;
}

.link-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  color: var(--builder-primary, #2563eb);
  text-decoration: none;
}

.link-btn:hover {
  text-decoration: underline;
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
