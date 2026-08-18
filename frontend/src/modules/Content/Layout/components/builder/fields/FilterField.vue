<template>
  <div class="filter-field">
    <BaseCollapsible :default-open="true">
      <template #title>
        <BaseLabel :muted="false" class="primary-label">
          <template #prefix><Droplets :size="14" /></template>
          {{ t('builder.settings.groups.filters.label') }}
        </BaseLabel>
      </template>

      <div class="field-content px-3 pb-2 flex flex-col gap-4">
<!-- Opacity -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.opacity') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.opacity" :min="0" :max="100" unit="%" :placeholder-value="100" />
        </div>

        <!-- Blur -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.blur') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.blur" :min="0" :max="50" unit="px" :placeholder-value="0" />
        </div>

        <!-- Brightness -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.brightness') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.brightness" :min="0" :max="200" unit="%" :placeholder-value="100" />
        </div>

        <!-- Contrast -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.contrast') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.contrast" :min="0" :max="200" unit="%" :placeholder-value="100" />
        </div>

        <!-- Saturate -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.saturate') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.saturate" :min="0" :max="200" unit="%" :placeholder-value="100" />
        </div>

        <!-- Grayscale -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.grayscale') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.grayscale" :min="0" :max="100" unit="%" :placeholder-value="0" />
        </div>

        <!-- Sepia -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.sepia') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.sepia" :min="0" :max="100" unit="%" :placeholder-value="0" />
        </div>
        
        <!-- Invert -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.invert') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.invert" :min="0" :max="100" unit="%" :placeholder-value="0" />
        </div>

        <!-- Hue Rotate -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.hue_rotate') }}</BaseLabel>
            <BaseSliderInput v-model.number="localValue.hue_rotate" :min="0" :max="360" unit="deg" :placeholder-value="0" />
        </div>

        <!-- Blend Mode -->
        <div class="control-row">
            <BaseLabel>{{ t('builder.settings.fields.blend_mode') }}</BaseLabel>
            <select class="builder-select w-full" v-model="localValue.blend_mode">
                 <option value="normal">Normal</option>
                 <option value="multiply">Multiply</option>
                 <option value="screen">Screen</option>
                 <option value="overlay">Overlay</option>
                 <option value="darken">Darken</option>
                 <option value="lighten">Lighten</option>
                 <option value="color-dodge">Color Dodge</option>
                 <option value="color-burn">Color Burn</option>
                 <option value="hard-light">Hard Light</option>
                 <option value="soft-light">Soft Light</option>
                 <option value="difference">Difference</option>
                 <option value="exclusion">Exclusion</option>
                 <option value="hue">Hue</option>
                 <option value="saturation">Saturation</option>
                 <option value="color">Color</option>
                 <option value="luminosity">Luminosity</option>
            </select>
        </div>
</div>
    </BaseCollapsible>
  </div>
</template>

<script setup lang="ts">
import { watch, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import Droplets from 'lucide-vue-next/dist/esm/icons/droplets.js';
import { BaseLabel, BaseSliderInput, BaseCollapsible } from '@/components/builder/ui'
import type { SettingDefinition } from '@/types/builder'

interface FilterState {
  opacity: number;
  blur: number;
  brightness: number;
  contrast: number;
  saturate: number;
  grayscale: number;
  sepia: number;
  hue_rotate: number;
  invert: number;
  blend_mode: string;
}

const props = defineProps<{
  field?: SettingDefinition;
  value: Partial<FilterState>;
  placeholderValue?: Partial<FilterState>;
}>()

const emit = defineEmits(['update:value'])
const { t } = useI18n()

// Use a local reactive object to track values. 
// We merge props.value into it.
const localValue = reactive<FilterState>({ 
    opacity: 100,
    blur: 0,
    brightness: 100,
    contrast: 100,
    saturate: 100,
    grayscale: 0,
    sepia: 0,
    hue_rotate: 0,
    invert: 0,
    blend_mode: 'normal'
})

watch(() => props.value, (newVal) => {
    if (newVal) {
        Object.keys(localValue).forEach(key => {
            const k = key as keyof FilterState
            if (newVal[k] !== undefined) {
                (localValue as Record<string, unknown>)[k] = newVal[k]
            }
        })
    }
}, { deep: true, immediate: true })

watch(localValue, (newVal) => {
    emit('update:value', { ...newVal })
}, { deep: true })

</script>

<style scoped>
.control-row {
    display: flex;
    flex-direction: column;
}
</style>
