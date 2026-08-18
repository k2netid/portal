<template>
  <div
    v-show="surfaceStyle === 'glass'"
    class="space-y-4 border-t border-border/40 pt-4"
  >
    <div>
      <h4 class="text-sm font-medium text-foreground">
        {{ t('system.settings.consoleAppearance.glassGradientSectionTitle') }}
      </h4>
      <p class="mt-0.5 text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.glassGradientSectionDescription') }}
      </p>
    </div>

    <ConsoleRichSelect
      :model-value="modelValue"
      :options="gradientOptions"
      :placeholder="t('system.settings.consoleAppearance.glassGradientSelectPlaceholder')"
      :aria-label="t('system.settings.consoleAppearance.glassGradientSectionTitle')"
      swatch-class="h-9 w-14 shrink-0"
      @update:model-value="emit('update:modelValue', $event as ConsoleGlassGradientPresetId)"
    />

    <div
      v-if="modelValue === 'custom'"
      class="space-y-4 rounded-lg border border-dashed border-border/70 bg-muted/10 p-3"
    >
      <div class="flex flex-wrap items-center gap-2">
        <ColorPicker
          v-model="gradientColor"
          :title="t('system.settings.consoleAppearance.glassGradientColorPickerTitle')"
        >
          <button
            type="button"
            class="h-9 w-9 shrink-0 rounded-md border border-border shadow-sm relative overflow-hidden"
            :style="{ backgroundColor: gradientColor }"
          />
        </ColorPicker>
        <Input
          v-model="gradientColor"
          class="h-9 w-[6.75rem] font-mono text-xs uppercase"
          maxlength="7"
        />
      </div>
      <div class="space-y-2">
        <div class="flex items-center justify-between text-xs">
          <span class="text-muted-foreground">{{ t('system.settings.consoleAppearance.glassGradientIntensityLabel') }}</span>
          <span class="tabular-nums text-foreground">{{ intensity }}%</span>
        </div>
        <input
          :value="intensity"
          type="range"
          min="0"
          max="100"
          class="console-range-input w-full accent-primary"
          @input="emit('update:intensity', Number(($event.target as HTMLInputElement).value))"
        >
      </div>
      <div class="space-y-2">
        <div class="flex items-center justify-between text-xs">
          <span class="text-muted-foreground">{{ t('system.settings.consoleAppearance.glassGradientAngleLabel') }}</span>
          <span class="tabular-nums text-foreground">{{ angle }}°</span>
        </div>
        <input
          :value="angle"
          type="range"
          min="0"
          max="360"
          class="console-range-input w-full accent-primary"
          @input="emit('update:angle', Number(($event.target as HTMLInputElement).value))"
        >
      </div>
    </div>

    <div
      class="h-24 rounded-xl border border-border/60 overflow-hidden"
      :style="previewStyle"
      aria-hidden="true"
    />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ColorPicker from '@/shared/components/ui/ColorPicker.vue';
import Input from '@/shared/components/ui/Input.vue';
import { ConsoleRichSelect } from '@/shared/components/shell';
import {
    CONSOLE_GLASS_GRADIENT_PRESETS,
    buildConsoleGlassBackgroundImage,
    glassGradientSwatchStyle,
    type ConsoleGlassGradientPresetId,
} from '@/modules/Core/System/constants/consoleGlassGradient';
import type { ConsoleSurfaceStyle } from '@/modules/Core/System/constants/consoleThemePresets';

const props = defineProps<{
    modelValue: ConsoleGlassGradientPresetId;
    surfaceStyle: ConsoleSurfaceStyle;
    gradientColor: string;
    intensity: number;
    angle: number;
    primaryHsl: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: ConsoleGlassGradientPresetId];
    'update:gradientColor': [value: string];
    'update:intensity': [value: number];
    'update:angle': [value: number];
}>();

const { t } = useI18n();

const gradientColor = computed({
    get: () => props.gradientColor,
    set: (v: string) => emit('update:gradientColor', v),
});

const gradientOptions = computed(() =>
    CONSOLE_GLASS_GRADIENT_PRESETS.map((preset) => ({
        value: preset.id,
        label: t(`system.settings.consoleAppearance.glassGradients.${preset.labelKey}.label`),
        description: t(`system.settings.consoleAppearance.glassGradients.${preset.labelKey}.description`),
        swatchStyle: glassGradientSwatchStyle(preset.id, props.primaryHsl, props.gradientColor),
    })),
);

const previewStyle = computed(() => {
    const bg = buildConsoleGlassBackgroundImage({
        preset: props.modelValue,
        colorHex: props.gradientColor,
        primaryHsl: props.primaryHsl,
        intensity: props.intensity,
        angle: props.angle,
    });
    return {
        backgroundColor: 'hsl(var(--background))',
        backgroundImage: bg === 'none' ? undefined : bg,
    };
});
</script>
