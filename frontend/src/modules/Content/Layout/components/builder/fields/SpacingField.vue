<template>
  <div class="spacing-field">
    <!-- Visual Box Model Layout (Simplified Grid) -->
    <div class="spacing-grid">
      <!-- Top -->
      <div v-for="side in sideKeys" :key="side" :class="['spacing-item', side]">
        <BaseLabel>{{ t(`builder.fields.spacing.sides.${side}`) }}</BaseLabel>
        <BaseInput 
          type="number" 
          :model-value="(localValue[side] as string | number)" 
          :placeholder="(localValue[side] === 0 || localValue[side] === '' ? (placeholderValue?.[side] as string ?? '') : '')"
          @update:model-value="updateValue(side, $event)"
          class="text-center"
        />
      </div>
      
      <!-- Link Toggle (Center/Overlay) -->
      <div class="spacing-link">
        <button 
          class="link-btn" 
          :class="{ active: isLinked }"
          @click="toggleLink"
          :title="t('builder.fields.spacing.link')"
        >
          <Link2 :size="14" />
        </button>
      </div>
    </div>

    <!-- Unit Selector -->
    <div class="unit-selector-wrapper" v-if="hasUnits">
      <BaseSegmentedControl
        :model-value="localValue.unit"
        @update:model-value="updateUnit"
        :options="unitOptions"
        size="sm"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import Link2 from 'lucide-vue-next/dist/esm/icons/link.js';
import { BaseLabel, BaseInput, BaseSegmentedControl } from '@/components/builder/ui'
import type { SettingDefinition } from '@/types/builder'

const { t } = useI18n()

const props = withDefaults(defineProps<{
  field: SettingDefinition;
  value: Record<string, unknown> | string;
  placeholderValue?: Record<string, unknown> | null;
}>(), {
  placeholderValue: null
})

const emit = defineEmits(['update:value'])

// State
const sideKeys = ['top', 'right', 'bottom', 'left']
const localValue = ref<Record<string, string | number>>({ top: 0, right: 0, bottom: 0, left: 0, unit: 'px' })
const isLinked = ref(false)

const hasUnits = computed(() => Array.isArray(props.field.units) && props.field.units.length > 0)

const unitOptions = computed(() => {
    return (props.field.units as string[] || []).map((u: string) => ({ label: u, value: u }))
})

// Sync with prop
watch(() => props.value, (newVal) => {
  if (typeof newVal === 'object' && newVal !== null) { localValue.value = { 
        top: (newVal as Record<string, unknown>).top as number ?? 0, 
        right: (newVal as Record<string, unknown>).right as number ?? 0, 
        bottom: (newVal as Record<string, unknown>).bottom as number ?? 0, 
        left: (newVal as Record<string, unknown>).left as number ?? 0,
        unit: (newVal as Record<string, unknown>).unit as string || 'px'
    }
  } else {
    localValue.value = { top: 0, right: 0, bottom: 0, left: 0, unit: 'px' }
  }
}, { immediate: true, deep: true })

const updateValue = (side: string, val: string | number | boolean) => {
  const num = parseFloat(val as string) || 0
  
  if (isLinked.value) {
    localValue.value.top = num
    localValue.value.right = num
    localValue.value.bottom = num
    localValue.value.left = num
  } else {
    localValue.value[side] = num
  }
  
  emitUpdate()
}

const updateUnit = (unit: string | number | boolean) => {
  localValue.value.unit = String(unit)
  emitUpdate()
}

const toggleLink = () => {
  isLinked.value = !isLinked.value
  if (isLinked.value) {
    const val = localValue.value.top
    localValue.value.right = val
    localValue.value.bottom = val
    localValue.value.left = val
    emitUpdate()
  }
}

const emitUpdate = () => {
  emit('update:value', { ...localValue.value })
}
</script>

<style scoped>
.spacing-field {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.spacing-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  gap: 8px;
  position: relative;
}

.spacing-item {
  display: flex;
  flex-direction: column;
}

/* Link Button in Center */
.spacing-link {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}

.link-btn {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  color: var(--builder-text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  transition: all 0.2s;
}

.link-btn:hover {
  background: var(--builder-bg-secondary);
  color: var(--builder-text-primary);
}

.link-btn.active {
  background: var(--builder-accent);
  color: white;
  border-color: var(--builder-accent);
}

.unit-selector-wrapper {
  display: flex;
  justify-content: flex-end;
}
</style>
