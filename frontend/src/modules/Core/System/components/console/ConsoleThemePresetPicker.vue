<template>
  <div class="space-y-4">
    <div>
      <h3 class="text-sm font-medium text-foreground">
        {{ t('system.settings.consoleAppearance.presetSectionTitle') }}
      </h3>
      <p class="mt-0.5 text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.presetSectionDescription') }}
      </p>
    </div>

    <ConsoleRichSelect
      :model-value="modelValue"
      :options="presetOptions"
      :placeholder="t('system.settings.consoleAppearance.presetSelectPlaceholder')"
      :aria-label="t('system.settings.consoleAppearance.presetSectionTitle')"
      @update:model-value="emit('update:modelValue', $event as ConsoleColorPresetId)"
    />

    <div
      v-if="modelValue === 'custom'"
      class="rounded-lg border border-dashed border-border/70 bg-muted/15 px-3 py-2.5"
    >
      <p class="mb-2 text-[11px] text-muted-foreground">
        {{ t('system.settings.consoleAppearance.customColorHint') }}
      </p>
      <slot name="custom-color" />
    </div>

    <div class="console-appearance-preview space-y-3 rounded-lg border border-border/60 bg-muted/20 p-3">
      <div class="flex flex-wrap items-center gap-2">
        <span class="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
          {{ t('system.settings.consoleAppearance.previewLabel') }}
        </span>
        <div
          class="inline-flex items-center gap-1 rounded-lg border border-border/50 bg-background/80 p-1"
          role="tablist"
          :aria-label="t('system.settings.consoleAppearance.previewLabel')"
        >
          <button
            v-for="tab in previewTabs"
            :key="tab.id"
            type="button"
            role="tab"
            :aria-selected="previewTab === tab.id"
            class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
            :class="previewTab === tab.id
              ? 'bg-primary text-primary-foreground shadow-sm'
              : 'text-muted-foreground hover:bg-muted/60 hover:text-foreground'"
            @click="previewTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>

      <div
        class="flex min-h-[2.75rem] items-center gap-3 rounded-md border border-border/50 bg-background px-3 py-2"
        role="tabpanel"
        :aria-label="activePreviewTab?.label"
      >
        <button
          v-if="previewTab === 'primary'"
          type="button"
          class="inline-flex h-9 cursor-default items-center bg-primary px-4 text-sm font-medium text-primary-foreground shadow-none"
          :style="{ borderRadius: previewRadius }"
          tabindex="0"
        >
          {{ t('system.settings.consoleAppearance.previewPrimary') }}
        </button>
        <button
          v-else-if="previewTab === 'secondary'"
          type="button"
          class="inline-flex h-9 cursor-default items-center border border-border bg-background px-4 text-sm font-medium text-foreground shadow-none"
          :style="{ borderRadius: previewRadius }"
          tabindex="0"
        >
          {{ t('system.settings.consoleAppearance.previewSecondary') }}
        </button>
        <a
          v-else
          href="#"
          class="text-sm font-medium text-primary underline-offset-4 hover:underline"
          @click.prevent
        >
          {{ t('system.settings.consoleAppearance.previewLink') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { ConsoleRichSelect } from '@/shared/components/shell';
import {
    CONSOLE_THEME_PRESETS,
    type ConsoleColorPresetId,
    type ConsoleThemePresetMeta,
} from '@/modules/Core/System/constants/consoleThemePresets';

type PreviewTabId = 'primary' | 'secondary' | 'link';

const props = withDefaults(
    defineProps<{
        modelValue: ConsoleColorPresetId;
        brandColor?: string;
        buttonRadius?: number;
    }>(),
    {
        brandColor: '#4f46e5',
        buttonRadius: 8,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: ConsoleColorPresetId];
}>();

const { t } = useI18n();
const previewTab = ref<PreviewTabId>('primary');

const previewTabs = computed(() => [
    { id: 'primary' as const, label: t('system.settings.consoleAppearance.previewPrimary') },
    { id: 'secondary' as const, label: t('system.settings.consoleAppearance.previewSecondary') },
    { id: 'link' as const, label: t('system.settings.consoleAppearance.previewLink') },
]);

const activePreviewTab = computed(() => previewTabs.value.find((tab) => tab.id === previewTab.value));
const previewRadius = computed(() => `${props.buttonRadius ?? 8}px`);

function swatchStyle(preset: ConsoleThemePresetMeta): Record<string, string> {
    if (preset.isCustom) {
        return { backgroundColor: props.brandColor || '#4f46e5' };
    }
    return { backgroundColor: `hsl(${preset.swatchHsl})` };
}

const presetOptions = computed(() =>
    CONSOLE_THEME_PRESETS.map((preset) => ({
        value: preset.id,
        label: t(`system.settings.consoleAppearance.presets.${preset.labelKey}.label`),
        description: t(`system.settings.consoleAppearance.presets.${preset.labelKey}.description`),
        swatchStyle: swatchStyle(preset),
    })),
);
</script>
