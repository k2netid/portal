<template>
  <div class="animation-field">
    <BaseCollapsible :default-open="true">
      <template #title>
        <BaseLabel :muted="false" class="primary-label">
          <template #prefix><PlayCircle :size="14" /></template>
          {{ t('builder.settings.groups.animation.label') }}
        </BaseLabel>
      </template>

      <div class="field-content px-3 pb-2 flex flex-col gap-4">
<!-- Animation Effect Selector -->
        <div class="control-row">
            <BaseLabel class="mb-2">{{ t('builder.settings.fields.animation_effect') }}</BaseLabel>
            <div class="grid grid-cols-2 gap-2">
                 <select class="builder-select w-full col-span-2" v-model="localValue.effect">
                     <option value="">None</option>
                     <optgroup label="Fade">
                        <option value="animate-fade">Fade In</option>
                        <option value="animate-fade-up">Fade In Up</option>
                        <option value="animate-fade-down">Fade In Down</option>
                        <option value="animate-fade-left">Fade In Left</option>
                        <option value="animate-fade-right">Fade In Right</option>
                     </optgroup>
                     <optgroup label="Zoom">
                        <option value="animate-zoom">Zoom In</option>
                     </optgroup>
                      <optgroup label="Bounce">
                        <option value="animate-bounce-in">Bounce In</option>
                     </optgroup>
                      <optgroup label="Flip">
                        <option value="animate-flip-x">Flip X</option>
                     </optgroup>
                 </select>
            </div>
        </div>

        <!-- Controls (Show only if effect is selected) -->
        <template v-if="localValue.effect">
<!-- Duration -->
            <div class="control-row">
                <BaseLabel>{{ t('builder.settings.fields.animation_duration') }}</BaseLabel>
                <BaseSliderInput v-model.number="localValue.duration" :min="0" :max="3000" :step="100" unit="ms" :placeholder-value="1000" />
            </div>

            <!-- Delay -->
            <div class="control-row">
                <BaseLabel>{{ t('builder.settings.fields.animation_delay') }}</BaseLabel>
                <BaseSliderInput v-model.number="localValue.delay" :min="0" :max="2000" :step="50" unit="ms" :placeholder-value="0" />
            </div>

            <!-- Repeat -->
             <div class="control-row">
                <BaseLabel>{{ t('builder.settings.fields.animation_repeat') }}</BaseLabel>
                 <select class="builder-select w-full" v-model="localValue.repeat">
                     <option value="once">{{ t('builder.settings.options.animation_repeat.1') }}</option>
                     <option value="infinite">{{ t('builder.settings.options.animation_repeat.infinite') }}</option>
                 </select>
            </div>
            
             <!-- Easing -->
             <div class="control-row">
                <BaseLabel>{{ t('builder.settings.fields.animation_curve') || t('builder.settings.fields.transition_curve') }}</BaseLabel>
                 <select class="builder-select w-full" v-model="localValue.curve">
                     <option value="ease">Ease</option>
                     <option value="ease-in">Ease In</option>
                     <option value="ease-out">Ease Out</option>
                     <option value="ease-in-out">Ease In Out</option>
                     <option value="linear">Linear</option>
                 </select>
            </div>
</template>
        
        <div v-else class="text-xs text-muted text-center py-2">
            {{ t('builder.settings.groups.animation.empty') || 'Select an animation style to see options.' }}
        </div>
</div>
    </BaseCollapsible>
  </div>
</template>

<script setup lang="ts">
import { watch, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import PlayCircle from 'lucide-vue-next/dist/esm/icons/circle-play.js';
import { BaseLabel, BaseSliderInput, BaseCollapsible } from '@/components/builder/ui'
import type { SettingDefinition } from '@/types/builder'

interface AnimationState {
  effect: string;
  duration: number;
  delay: number;
  repeat: string;
  curve: string;
}

const props = defineProps<{
  field?: SettingDefinition;
  value: Record<string, unknown>;
  placeholderValue?: unknown;
}>()

const emit = defineEmits(['update:value'])
const { t } = useI18n()

// Local state
const localValue = reactive<AnimationState>({ 
    effect: '',
    duration: 1000,
    delay: 0,
    repeat: 'once',
    curve: 'ease'
})

watch(() => props.value, (newVal) => {
    if (newVal) {
        Object.keys(localValue).forEach(key => {
            const k = key as keyof AnimationState
            if (newVal[k] !== undefined) {
                (localValue as unknown as Record<string, unknown>)[k] = newVal[k]
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
