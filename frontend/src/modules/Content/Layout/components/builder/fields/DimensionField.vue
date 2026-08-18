<template>
  <div class="dimension-field">
    <div class="flex items-center gap-2 mb-3">
        <!-- Input with Spinners -->
        <div class="flex-1 relative flex items-center separate-input-box">
            <input 
                type="text" 
                v-model="displayValue"
                @blur="handleBlur"
                class="base-input w-full pr-8"
                :placeholder="placeholderValue || 'auto'"
            />
            <div v-if="unit !== 'auto'" class="input-spinners-inline">
                <button class="spinner-inline-btn" @click="updateNumericValue(1)">
                    <ChevronUp :size="12" />
                </button>
                <button class="spinner-inline-btn" @click="updateNumericValue(-1)">
                    <ChevronDown :size="12" />
                </button>
            </div>
        </div>
        
        <!-- Unit Dropdown -->
        <div class="separate-unit-dropdown">
            <BaseDropdown align="right" width="auto" :min-width="80">
                <template #trigger="{ open }">
                    <div class="unit-trigger" :class="{ 'is-open': open }">
                        <span>{{ unit }}</span>
                        <ChevronDown :size="12" />
                    </div>
                </template>
                
                <template #default="{ close }">
                    <div 
                        v-for="u in activeUnits" 
                        :key="u"
                        class="unit-item"
                        :class="{ active: unit === u }"
                        @click="setUnit(u); close()"
                    >
                        {{ u }}
                    </div>
                </template>
            </BaseDropdown>
        </div>
    </div>

    <!-- Slider (Only if not auto) -->
    <div v-if="showSlider" class="px-1">
        <BaseSlider 
            :min="0" 
            :max="sliderMax" 
            :model-value="numericValue" 
            @update:model-value="updateValueFromSlider"
        />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import ChevronUp from 'lucide-vue-next/dist/esm/icons/chevron-up.js';
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import { BaseSlider, BaseDropdown } from '@/components/builder/ui'
import type { SettingDefinition } from '@/types/builder'

const props = defineProps<{
  field: SettingDefinition;
  value: string;
  placeholderValue?: string | null;
  units?: string[] | null;
}>()

const activeUnits = computed(() => {
    if (props.units) return props.units
    if (props.field?.options) return props.field.options as string[]
    return ['px', '%', 'em', 'rem', 'vw', 'vh', 'auto']
})

const emit = defineEmits(['update:value'])

const numericValue = ref(0)
const unit = ref('px')

const keywords = ['auto', 'inherit', 'initial', 'unset', 'normal', 'none', 'cover', 'contain']

// Extract numeric value and unit from string
const parseValue = (val: string) => {
    if (!val || keywords.includes(val)) {
        numericValue.value = 0
        unit.value = val || 'auto'
        return
    }

    // Default processing
    const num = parseFloat(val)
    const u = val.replace(/[0-9.-]/g, '') || 'px'
    
    numericValue.value = isNaN(num) ? 0 : num
    unit.value = u
}

const displayValue = computed({
    get: () => {
        if (keywords.includes(unit.value)) return unit.value
        return numericValue.value
    },
    set: (val: string | number) => {
        const lowerVal = String(val).toLowerCase()
        if (keywords.includes(lowerVal)) {
            unit.value = lowerVal
            numericValue.value = 0
            emitUpdate()
            return
        }
        
        const num = parseFloat(String(val))
        if (!isNaN(num)) {
            numericValue.value = num
            if (keywords.includes(unit.value)) unit.value = 'px'
        }
        emitUpdate()
    }
})

const sliderMax = computed(() => {
    if (unit.value === '%') return 100
    if (unit.value === 'vw' || unit.value === 'vh') return 100
    return 1000 // default for px, em, etc.
})

const setUnit = (u: string) => {
    unit.value = u
    if (keywords.includes(u)) numericValue.value = 0
    emitUpdate()
}

const updateNumericValue = (delta: number) => {
    numericValue.value += delta
    emitUpdate()
}

const showSlider = computed(() => {
    return !keywords.includes(unit.value) && unit.value !== ''
})

const updateValueFromSlider = (val: number) => {
    numericValue.value = val
    emitUpdate()
}

const emitUpdate = () => {
    const finalVal = keywords.includes(unit.value) ? unit.value : `${numericValue.value}${unit.value}`
    emit('update:value', finalVal)
}

const handleBlur = () => {
    if (displayValue.value === '') {
        // Fallback or keep empty
    }
}

watch(() => props.value, (newVal) => {
    parseValue(newVal)
}, { immediate: true })

</script>

<style scoped>
.dimension-field {
    width: 100%;
}

.separate-input-box {
    display: flex;
    align-items: center;
    border: 1px solid var(--builder-field-border-clean);
    background: var(--builder-bg-primary);
    border-radius: 4px;
    height: 32px;
}

.separate-unit-dropdown {
    height: 32px;
}

.separate-unit-dropdown .unit-trigger {
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 10px;
    font-size: 12px;
    color: var(--builder-text-secondary);
    cursor: pointer;
    background: var(--builder-bg-primary);
    border: 1px solid var(--builder-field-border-clean);
    border-radius: 4px;
    min-width: 70px;
    box-sizing: border-box;
}

.unit-trigger:hover {
    color: var(--builder-text-primary);
}

.unit-item {
    padding: 6px 12px;
    font-size: 11px;
    color: var(--builder-text-secondary);
    cursor: pointer;
}

.unit-item:hover, .unit-item.active {
    background: var(--builder-bg-primary);
    color: var(--builder-text-primary);
}

.input-spinners-inline {
    display: flex;
    flex-direction: column;
    height: 100%;
    border-right: 1px solid var(--builder-field-border-clean);
    padding: 0 4px;
}

.spinner-inline-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--builder-text-muted);
}

.spinner-inline-btn:hover {
    color: var(--builder-accent);
}

.base-input {
    background: transparent !important;
    border: none !important;
    height: 100%;
    font-size: 13px;
    color: var(--builder-text-primary);
    outline: none;
    padding: 0 8px;
}
</style>
