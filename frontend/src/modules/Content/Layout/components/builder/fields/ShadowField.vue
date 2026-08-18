<template>
  <div class="shadow-field">
    <BaseCollapsible :default-open="true">
      <template #title>
        <BaseLabel :muted="false" class="primary-label">
          <template #prefix><Layers :size="14" /></template>
          Box Shadow
        </BaseLabel>
      </template>

      <div class="field-content px-3 pb-2">
        <!-- Presets -->
        <BaseLabel class="mb-3">{{ t('builder.fields.shadow.presets.label') }}</BaseLabel>
        <div class="presets-grid grid grid-cols-4 gap-3 mb-2">
          <button 
            v-for="(preset, key) in presets" 
            :key="key"
            class="preset-btn aspect-square rounded-md border flex items-center justify-center transition-colors bg-white"
            :class="{ 'is-active': localValue.preset === key, 'is-inactive': localValue.preset !== key }"
            @click="applyPreset(key)"
            :title="preset.label"
          >
            <div v-if="key === 'none'" class="none-icon">
                <Ban :size="18" />
            </div>
            <div v-else class="preview-box" :style="preset.style"></div>
          </button>
        </div>

        <!-- Manual Controls -->
        <div v-if="localValue.preset !== 'none'" class="manual-controls flex flex-col gap-4 mt-4">
            <div class="control-row">
                <BaseLabel>{{ t('builder.fields.shadow.controls.horizontal') }}</BaseLabel>
                <BaseSliderInput v-model.number="localValue.horizontal" :min="-100" :max="100" :placeholder-value="placeholderValue?.horizontal" unit="px" />
            </div>

            <div class="control-row">
                <BaseLabel>{{ t('builder.fields.shadow.controls.vertical') }}</BaseLabel>
                <BaseSliderInput v-model.number="localValue.vertical" :min="-100" :max="100" :placeholder-value="placeholderValue?.vertical" unit="px" />
            </div>

            <div class="control-row">
                <BaseLabel>{{ t('builder.fields.shadow.controls.blur') }}</BaseLabel>
                <BaseSliderInput v-model.number="localValue.blur" :min="0" :max="100" :placeholder-value="placeholderValue?.blur" unit="px" />
            </div>

            <div class="control-row">
                <BaseLabel>{{ t('builder.fields.shadow.controls.spread') }}</BaseLabel>
                <BaseSliderInput v-model.number="localValue.spread" :min="-100" :max="100" :placeholder-value="placeholderValue?.spread" unit="px" />
            </div>

            <div class="control-row">
                 <BaseLabel>{{ t('builder.fields.shadow.controls.color') }}</BaseLabel>
                 <ColorField 
                   :value="localValue.color" 
                   :placeholder-value="placeholderValue?.color"
                   @update:value="(val: string) => localValue.color = val" 
                 />
            </div>

            <div class="control-row flex items-center justify-between">
                <BaseLabel :muted="false" :uppercase="false">{{ t('builder.fields.shadow.controls.inset') }}</BaseLabel>
                <BaseToggle v-model="localValue.inset" />
            </div>
        </div>
      </div>
    </BaseCollapsible>
  </div>
</template>

<script setup lang="ts">
import { reactive, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import Ban from 'lucide-vue-next/dist/esm/icons/ban.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import { BaseLabel, BaseSliderInput, BaseToggle, BaseCollapsible } from '@/components/builder/ui'
import ColorField from '@/components/builder/fields/ColorField.vue'
import type { SettingDefinition } from '@/types/builder'


interface ShadowValue {
  preset: string;
  horizontal: number; 
  vertical: number; 
  blur: number; 
  spread: number; 
  color: string; 
  inset: boolean;
}

const props = withDefaults(defineProps<{
  field: SettingDefinition;
  value: ShadowValue;
  placeholderValue?: ShadowValue | null;
}>(), {
  placeholderValue: null
})

const emit = defineEmits(['update:value'])
const { t } = useI18n()

// Presets Definition 
const presets = computed(() => ({
    none: { 
        label: t('builder.fields.shadow.presets.none'), 
        style: {}, 
        values: { preset: 'none', horizontal: 0, vertical: 0, blur: 0, spread: 0, color: 'rgba(0,0,0,0)', inset: false } 
    },
    outerSmall: { 
        label: t('builder.fields.shadow.presets.sm'), 
        style: { boxShadow: '0 2px 10px rgba(0,0,0,0.15)' }, 
        values: { preset: 'outerSmall', horizontal: 0, vertical: 2, blur: 10, spread: 0, color: 'rgba(0,0,0,0.15)', inset: false } 
    },
    outerLarge: { 
        label: t('builder.fields.shadow.presets.lg'), 
        style: { boxShadow: '0 10px 40px rgba(0,0,0,0.2)' }, 
        values: { preset: 'outerLarge', horizontal: 0, vertical: 10, blur: 40, spread: 0, color: 'rgba(0,0,0,0.2)', inset: false } 
    },
    offsetBottom: { 
        label: t('builder.fields.shadow.presets.xl'), 
        style: { boxShadow: '0 15px 25px -10px rgba(0,0,0,0.3)' }, 
        values: { preset: 'offsetBottom', horizontal: 0, vertical: 15, blur: 25, spread: -10, color: 'rgba(0,0,0,0.3)', inset: false } 
    },
    diagonal: { 
        label: 'Diagonal', 
        style: { boxShadow: '10px 10px 20px -5px rgba(0,0,0,0.25)' }, 
        values: { preset: 'diagonal', horizontal: 10, vertical: 10, blur: 20, spread: -5, color: 'rgba(0,0,0,0.25)', inset: false } 
    },
    spread: { 
        label: 'Spread', 
        style: { boxShadow: '0 0 0 10px rgba(0,0,0,0.1)' }, 
        values: { preset: 'spread', horizontal: 0, vertical: 0, blur: 0, spread: 10, color: 'rgba(0,0,0,0.1)', inset: false } 
    },
    insetSmall: { 
        label: t('builder.fields.shadow.presets.inner') + ' (S)', 
        style: { boxShadow: 'inset 0 2px 8px rgba(0,0,0,0.15)' }, 
        values: { preset: 'insetSmall', horizontal: 0, vertical: 2, blur: 8, spread: 0, color: 'rgba(0,0,0,0.15)', inset: true } 
    },
    insetLarge: { 
        label: t('builder.fields.shadow.presets.inner') + ' (L)', 
        style: { boxShadow: 'inset 0 10px 25px rgba(0,0,0,0.2)' }, 
        values: { preset: 'insetLarge', horizontal: 0, vertical: 10, blur: 25, spread: 0, color: 'rgba(0,0,0,0.2)', inset: true } 
    },
}))

const localValue = reactive({ ...props.value })

watch(() => props.value, (newVal) => {
    if (newVal) {
        Object.assign(localValue, newVal)
    }
}, { deep: true })

watch(localValue, (newVal) => {
    emit('update:value', { ...newVal })
}, { deep: true })

const applyPreset = (key: string) => {
    localValue.preset = key
    const p = (presets.value as Record<string, { values: ShadowValue }>)[key].values
    Object.assign(localValue, p)
}
</script>

<style scoped>
.presets-grid {
    padding: 4px;
}

.preset-btn {
    border: 1px solid var(--builder-border);
    cursor: pointer;
    position: relative;
    overflow: visible;
}

.preset-btn.is-active {
    border-color: #2563eb;
    box-shadow: 0 0 0 1px #2563eb;
}

.preset-btn.is-inactive:hover {
    border-color: rgba(0,0,0,0.2);
}

.none-icon {
    color: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-box {
    width: 20px;
    height: 20px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: transform 0.2s;
}

.preset-btn:hover .preview-box {
    transform: scale(1.05);
}

.primary-label {
    color: #2563eb !important;
}

.control-row {
    display: flex;
    flex-direction: column;
}
</style>
