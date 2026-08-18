<template>
  <div class="divide-y divide-border/30">
          <section class="space-y-4 p-6">
            <ConsoleThemePresetPicker
              v-model="colorPreset"
              :brand-color="brandColor"
              :button-radius="form.console_button_radius as number"
            >
              <template #custom-color>
                <div class="flex flex-wrap items-center gap-6">
                  <!-- Light Mode Color Picker -->
                  <div class="space-y-1.5">
                    <span class="text-xs font-semibold text-foreground/80 uppercase tracking-wider">
                      {{ t('system.settings.consoleAppearance.brandColorHexLabel') }}
                    </span>
                    <div class="flex items-center gap-2">
                      <ColorPicker
                        v-model="brandColor"
                        :title="t('system.settings.consoleAppearance.brandColorPickerTitle')"
                      >
                        <button
                          type="button"
                          class="h-9 w-9 shrink-0 rounded-md border border-border shadow-sm relative overflow-hidden cursor-pointer transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50"
                          :style="{ backgroundColor: brandColor }"
                          :aria-label="t('system.settings.consoleAppearance.brandColorPickerTitle')"
                        >
                          <span class="absolute inset-0 bg-gradient-to-br from-transparent to-black/10" />
                        </button>
                      </ColorPicker>
                      <Input
                        v-model="brandColor"
                        class="h-9 w-[6.75rem] font-mono text-xs uppercase"
                        maxlength="7"
                        spellcheck="false"
                        :aria-label="t('system.settings.consoleAppearance.brandColorHexLabel')"
                      />
                    </div>
                    <!-- Light Mode Contrast Badge -->
                    <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold">
                      <span class="text-muted-foreground">{{ t('system.settings.consoleAppearance.contrastRatio') }}:</span>
                      <span class="font-mono text-foreground">{{ lightModeContrast.ratio }}:1</span>
                      <span
                        v-if="lightModeContrast.passAAA"
                        class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"
                      >
                        {{ t('system.settings.consoleAppearance.contrastPassAAA') }}
                      </span>
                      <span
                        v-else-if="lightModeContrast.passAA"
                        class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20"
                      >
                        {{ t('system.settings.consoleAppearance.contrastPassAA') }}
                      </span>
                      <span
                        v-else
                        class="px-1.5 py-0.5 rounded bg-destructive/10 text-destructive border border-destructive/20"
                      >
                        {{ t('system.settings.consoleAppearance.contrastFail') }}
                      </span>
                    </div>
                  </div>

                  <!-- Dark Mode Color Picker (NEW) -->
                  <div class="space-y-1.5">
                    <span class="text-xs font-semibold text-foreground/80 uppercase tracking-wider">
                      {{ t('system.settings.consoleAppearance.brandColorDarkPickerTitle') }}
                    </span>
                    <div class="flex items-center gap-2">
                      <ColorPicker
                        v-model="brandColorDark"
                        :title="t('system.settings.consoleAppearance.brandColorDarkPickerTitle')"
                      >
                        <button
                          type="button"
                          class="h-9 w-9 shrink-0 rounded-md border border-border shadow-sm relative overflow-hidden cursor-pointer transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50"
                          :style="{ backgroundColor: brandColorDark }"
                          :aria-label="t('system.settings.consoleAppearance.brandColorDarkPickerTitle')"
                        >
                          <span class="absolute inset-0 bg-gradient-to-br from-transparent to-black/10" />
                        </button>
                      </ColorPicker>
                      <Input
                        v-model="brandColorDark"
                        class="h-9 w-[6.75rem] font-mono text-xs uppercase"
                        maxlength="7"
                        spellcheck="false"
                        :aria-label="t('system.settings.consoleAppearance.brandColorDarkPickerTitle')"
                      />
                    </div>
                    <!-- Dark Mode Contrast Badge -->
                    <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold">
                      <span class="text-muted-foreground">{{ t('system.settings.consoleAppearance.contrastRatio') }}:</span>
                      <span class="font-mono text-foreground">{{ darkModeContrast.ratio }}:1</span>
                      <span
                        v-if="darkModeContrast.passAAA"
                        class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"
                      >
                        {{ t('system.settings.consoleAppearance.contrastPassAAA') }}
                      </span>
                      <span
                        v-else-if="darkModeContrast.passAA"
                        class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20"
                      >
                        {{ t('system.settings.consoleAppearance.contrastPassAA') }}
                      </span>
                      <span
                        v-else
                        class="px-1.5 py-0.5 rounded bg-destructive/10 text-destructive border border-destructive/20"
                      >
                        {{ t('system.settings.consoleAppearance.contrastFail') }}
                      </span>
                    </div>
                  </div>
                </div>
              </template>
            </ConsoleThemePresetPicker>
          </section>

          <section class="space-y-4 p-6">
            <div>
              <h3 class="text-sm font-medium text-foreground">
                {{ t('system.settings.consoleAppearance.surfaceSectionTitle') }}
              </h3>
              <p class="mt-0.5 text-xs text-muted-foreground">
                {{ t('system.settings.consoleAppearance.surfaceSectionDescription') }}
              </p>
            </div>
            <div
              class="grid max-w-sm grid-cols-2 gap-2 rounded-xl border border-border/50 bg-muted/20 p-1.5"
              role="radiogroup"
              :aria-label="t('system.settings.consoleAppearance.surfaceSectionTitle')"
            >
              <button
                type="button"
                role="radio"
                :aria-checked="surfaceStyle === CONSOLE_SURFACE_GLASS"
                class="min-h-10 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                :class="surfaceStyle === CONSOLE_SURFACE_GLASS
                  ? 'bg-primary text-primary-foreground shadow-sm'
                  : 'text-muted-foreground hover:bg-muted/50 hover:text-foreground'"
                @click="surfaceStyle = CONSOLE_SURFACE_GLASS"
              >
                {{ t('system.settings.consoleAppearance.surfaceGlass') }}
              </button>
              <button
                type="button"
                role="radio"
                :aria-checked="surfaceStyle === CONSOLE_SURFACE_FLAT"
                class="min-h-10 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                :class="surfaceStyle === CONSOLE_SURFACE_FLAT
                  ? 'bg-primary text-primary-foreground shadow-sm'
                  : 'text-muted-foreground hover:bg-muted/50 hover:text-foreground'"
                @click="surfaceStyle = CONSOLE_SURFACE_FLAT"
              >
                {{ t('system.settings.consoleAppearance.surfaceFlat') }}
              </button>
            </div>
            <p
              v-if="surfaceStyle === CONSOLE_SURFACE_GLASS"
              class="text-xs text-foreground/80"
            >
              {{ t('system.settings.consoleAppearance.surfaceGlassHint') }}
            </p>
            <p
              v-else
              class="text-xs text-foreground/80"
            >
              {{ t('system.settings.consoleAppearance.surfaceFlatHint') }}
            </p>

            <ConsoleGlassGradientPicker
              v-model="glassGradientPreset"
              :surface-style="surfaceStyle"
              v-model:gradient-color="glassGradientColor"
              :intensity="clampGlassIntensity(form.console_glass_gradient_intensity)"
              :angle="clampGlassAngle(form.console_glass_gradient_angle)"
              :primary-hsl="primaryHsl"
              @update:intensity="form.console_glass_gradient_intensity = $event"
              @update:angle="form.console_glass_gradient_angle = $event"
            />
          </section>

          <section class="space-y-4 p-6">
            <div>
              <h3 class="text-sm font-medium text-foreground">
                {{ t('system.settings.consoleAppearance.shapeSectionTitle') }}
              </h3>
              <p class="mt-0.5 text-xs text-muted-foreground">
                {{ t('system.settings.consoleAppearance.buttonRadiusDescription') }}
              </p>
            </div>
            <div class="flex items-center gap-4 max-w-md">
              <label
                for="console_button_radius"
                class="sr-only"
              >{{ t('system.settings.consoleAppearance.buttonRadiusLabel') }}</label>
              <input
                id="console_button_radius"
                v-model.number="form.console_button_radius"
                type="range"
                min="0"
                max="24"
                :aria-label="t('system.settings.consoleAppearance.brandRadiusLabel')" class="console-range-input flex-1 h-2 cursor-pointer accent-primary"
              >
              <span class="w-12 text-right text-sm tabular-nums text-muted-foreground">{{ form.console_button_radius }}px</span>
            </div>
          </section>

    <section class="space-y-4 p-6 border-t border-border/40 bg-muted/5">
      <div>
        <h3 class="text-xs font-semibold text-foreground uppercase tracking-wider">
          {{ t('system.settings.consoleAppearance.themePortabilityTitle') }}
        </h3>
        <p class="mt-0.5 text-xs text-foreground/80">
          {{ t('system.settings.consoleAppearance.themePortabilityDescription') }}
        </p>
      </div>
      <div class="space-y-3 max-w-xl">
        <div class="flex gap-2">
          <Input readonly :value="exportedThemeJson" :aria-label="t('system.settings.consoleAppearance.themeExportJsonLabel')" class="h-9 flex-1 font-mono-selectable bg-muted/30 font-mono text-[11px]" />
          <Button type="button" variant="outline" size="sm" class="h-9 px-3" @click="copyThemeConfig">{{ t('common.actions.copy') }}</Button>
        </div>
        <div class="flex items-center gap-2">
          <Input v-model="importTarget" :placeholder="t('system.settings.consoleAppearance.themeImportPlaceholder')" class="h-9 flex-1 font-mono text-[11px]" />
          <Button type="button" size="sm" variant="secondary" class="h-9 px-3" @click="importThemeConfig">{{ t('system.settings.consoleAppearance.themeImportApply') }}</Button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import ConsoleThemePresetPicker from '@/modules/Core/System/components/console/ConsoleThemePresetPicker.vue';
import ConsoleGlassGradientPicker from '@/modules/Core/System/components/console/ConsoleGlassGradientPicker.vue';
import ColorPicker from '@/shared/components/ui/ColorPicker.vue';
import Input from '@/shared/components/ui/Input.vue';
import Button from '@/shared/components/ui/Button.vue';
import { useI18n } from 'vue-i18n';
import { useConsoleAppearanceContext } from '../composables/useConsoleAppearancePage';
import { CONSOLE_SURFACE_FLAT } from '@/modules/Core/System/constants/consoleThemePresets';
const { t } = useI18n();
const { form, colorPreset, surfaceStyle, brandColor, brandColorDark, glassGradientPreset, glassGradientColor, primaryHsl, lightModeContrast, darkModeContrast, exportedThemeJson, importTarget, clampGlassIntensity, clampGlassAngle, copyThemeConfig, importThemeConfig, CONSOLE_SURFACE_GLASS } = useConsoleAppearanceContext();
</script>
