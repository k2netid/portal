<template>
  <section class="space-y-6 p-6">
    <div>
      <h3 class="text-sm font-medium text-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.buttons.title') }}
      </h3>
      <p class="mt-0.5 text-xs text-muted-foreground">
        {{ t('system.settings.consoleAppearance.advancedComponents.buttons.description') }}
      </p>
    </div>

    <div class="flex flex-wrap items-center gap-6">
      <div class="space-y-1.5">
        <span class="text-xs font-semibold uppercase tracking-wider text-foreground/80">
          {{ t('system.settings.consoleAppearance.brandColorHexLabel') }}
        </span>
        <div class="flex items-center gap-2">
          <ColorPicker v-model="brandColor" :title="t('system.settings.consoleAppearance.brandColorPickerTitle')">
            <button
              type="button"
              class="relative h-9 w-9 shrink-0 cursor-pointer overflow-hidden rounded-md border border-border shadow-sm"
              :style="{ backgroundColor: brandColor }"
            />
          </ColorPicker>
          <Input v-model="brandColor" class="h-9 w-[6.75rem] font-mono text-xs uppercase" maxlength="7" />
        </div>
      </div>
      <div class="space-y-1.5">
        <span class="text-xs font-semibold uppercase tracking-wider text-foreground/80">
          {{ t('system.settings.consoleAppearance.brandColorDarkPickerTitle') }}
        </span>
        <div class="flex items-center gap-2">
          <ColorPicker v-model="brandColorDark" :title="t('system.settings.consoleAppearance.brandColorDarkPickerTitle')">
            <button
              type="button"
              class="relative h-9 w-9 shrink-0 cursor-pointer overflow-hidden rounded-md border border-border shadow-sm"
              :style="{ backgroundColor: brandColorDark }"
            />
          </ColorPicker>
          <Input v-model="brandColorDark" class="h-9 w-[6.75rem] font-mono text-xs uppercase" maxlength="7" />
        </div>
      </div>
    </div>

    <div class="grid max-w-2xl grid-cols-1 gap-4 sm:grid-cols-2">
      <div class="space-y-2">
        <label for="console_button_style" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          {{ t('system.settings.consoleAppearance.advancedComponents.buttons.styleLabel') }}
        </label>
        <select
          id="console_button_style"
          v-model="form.console_button_style"
          class="h-10 w-full rounded-lg border border-border/70 bg-background px-3 text-sm"
        >
          <option v-for="opt in buttonStyleOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
      <div class="space-y-2">
        <label for="adv_button_radius" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
          {{ t('system.settings.consoleAppearance.buttonRadiusLabel') }}
        </label>
        <div class="flex items-center gap-4">
          <input
            id="adv_button_radius"
            v-model.number="form.console_button_radius"
            type="range"
            min="0"
            max="24"
            class="console-range-input h-2 flex-1 accent-primary"
          >
          <span class="w-12 text-right text-sm tabular-nums text-muted-foreground">{{ form.console_button_radius }}px</span>
        </div>
      </div>
    </div>

    <div class="flex flex-wrap gap-2 rounded-lg border border-border/50 bg-muted/20 p-3">
      <button
        v-for="preview in buttonPreviews"
        :key="preview"
        type="button"
        class="inline-flex h-9 cursor-default items-center px-4 text-sm font-medium"
        :class="preview === 'solid' ? 'bg-primary text-primary-foreground' : preview === 'soft' ? 'border border-primary/25 bg-primary/15 text-primary' : 'border border-primary/45 bg-transparent text-primary'"
        :style="{ borderRadius: `${form.console_button_radius}px` }"
        tabindex="-1"
      >
        {{ t('system.settings.consoleAppearance.previewPrimary') }}
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ColorPicker from '@/shared/components/ui/ColorPicker.vue';
import Input from '@/shared/components/ui/Input.vue';
import { useConsoleAppearanceContext } from '../../composables/useConsoleAppearancePage';

const { t } = useI18n();
const { form, brandColor, brandColorDark } = useConsoleAppearanceContext();

const buttonStyleOptions = computed(() => [
    { value: 'solid', label: t('system.settings.consoleAppearance.advancedComponents.buttons.styles.solid') },
    { value: 'soft', label: t('system.settings.consoleAppearance.advancedComponents.buttons.styles.soft') },
    { value: 'outline', label: t('system.settings.consoleAppearance.advancedComponents.buttons.styles.outline') },
]);

const buttonPreviews = ['solid', 'soft', 'outline'] as const;
</script>
