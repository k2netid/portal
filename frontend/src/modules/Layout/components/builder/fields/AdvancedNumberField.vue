<template>
  <div class="advanced-number-field">
    <div class="flex items-center gap-2 mb-3">
        <!-- Input with Spinners -->
        <div class="flex-1 relative flex items-center advanced-number-input-box">
            <input 
                type="text" 
                v-model="displayValue"
                @blur="handleBlur"
                @input="handleInput"
                class="base-input w-full pr-8"
                :placeholder="(placeholderValue as string) || '0'"
            />
            <div class="input-spinners-inline">
                <button class="spinner-inline-btn" @click="updateNumericValue(step)">
                    <ChevronUp :size="12" />
                </button>
                <button class="spinner-inline-btn" @click="updateNumericValue(-step)">
                    <ChevronDown :size="12" />
                </button>
            </div>
        </div>
        
        <!-- Option Dropdown -->
        <div class="advanced-option-dropdown">
            <BaseDropdown align="right" width="auto" :min-width="100">
                <template #trigger="{ open }">
                    <div class="option-trigger" :class="{ 'is-open': open }">
                        <span>{{ currentOptionLabel }}</span>
                        <ChevronDown :size="12" />
                    </div>
                </template>
                
                <template #default="{ close }">
                    <div 
                        v-for="opt in options" 
                        :key="opt.value"
                        class="option-item"
                        :class="{ active: currentOption === opt.value }"
                        @click="setOption(opt.value); close()"
                    >
                        {{ opt.label }}
                    </div>
                </template>
            </BaseDropdown>
        </div>
    </div>

    <!-- Slider -->
    <div class="slider-wrapper px-1">
        <BaseSlider 
            :min="min" 
            :max="max" 
            :step="step"
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

const props = defineProps({
  field: { type: Object as () => SettingDefinition, default: () => ({}) as SettingDefinition },
  value: { type: [String, Number], default: '' },
  placeholderValue: { type: [String, Number], default: null }
})

const emit = defineEmits(['update:value'])

const min = computed(() => props.field.min ?? -10)
const max = computed(() => props.field.max ?? 10)
const step = computed(() => props.field.step ?? 1)

const options = [
    { label: '-', value: 'default' },
    { label: 'inherit', value: 'inherit' },
    { label: 'unset', value: 'unset' },
    { label: 'css var', value: 'var' }
]

const numericValue = ref(0)
const currentOption = ref('default')

const currentOptionLabel = computed(() => {
    return options.find(o => o.value === currentOption.value)?.label || '-'
})

// Parse initial value
const parseValue = (val: string | number | null | undefined) => {
    if (val === null || val === undefined || val === '') {
        currentOption.value = 'default'
        numericValue.value = 0
        return
    }

    if (val === 'inherit' || val === 'unset') {
        currentOption.value = val
        numericValue.value = 0
        return
    }

    if (typeof val === 'string' && val.startsWith('var(')) {
        currentOption.value = 'var'
        numericValue.value = 0
        return
    }

    const num = parseFloat(String(val))
    if (!isNaN(num)) {
        numericValue.value = num
        currentOption.value = 'default'
    } else {
        currentOption.value = 'default'
        numericValue.value = 0
    }
}

const displayValue = computed({
    get: () => {
        if (currentOption.value === 'inherit' || currentOption.value === 'unset' || currentOption.value === 'var') {
            return props.value || ''
        }
        return numericValue.value
    },
    set: (val: string | number) => {
        const num = parseFloat(val as string)
        if (!isNaN(num)) {
            numericValue.value = num
            currentOption.value = 'default'
            emitUpdate()
        }
    }
})

const handleInput = (_e: Event) => {
    // Basic validation could be added here
}

const handleBlur = () => {
    // Ensure value is emitted on blur if changed manually
    emitUpdate()
}

const updateNumericValue = (delta: number) => {
    numericValue.value += delta
    currentOption.value = 'default'
    emitUpdate()
}

const updateValueFromSlider = (val: number) => {
    numericValue.value = val
    currentOption.value = 'default'
    emitUpdate()
}

const setOption = (opt: string) => {
    currentOption.value = opt
    if (opt === 'default') {
        emitUpdate()
    } else if (opt === 'var') {
        // Placeholder for var, ideally opens a prompt or similar
        emit('update:value', 'var(--custom)')
    } else {
        emit('update:value', opt)
    }
}

const emitUpdate = () => {
    if (currentOption.value === 'default') {
        emit('update:value', numericValue.value)
    }
    // Other options are emitted directly in setOption
}

watch(() => props.value, (newVal) => {
    parseValue(newVal)
}, { immediate: true })

</script>

<style scoped>
.advanced-number-field {
    width: 100%;
}

.advanced-number-input-box {
    display: flex;
    align-items: center;
    border: 1px solid var(--builder-field-border-clean);
    background: var(--builder-bg-primary);
    border-radius: 4px;
    height: 32px;
}

.advanced-option-dropdown {
    height: 32px;
}

.advanced-option-dropdown .option-trigger {
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
    min-width: 80px;
    box-sizing: border-box;
}

.option-trigger:hover {
    color: var(--builder-text-primary);
    border-color: var(--builder-accent);
}

.option-item {
    padding: 8px 12px;
    font-size: 12px;
    color: var(--builder-text-secondary);
    cursor: pointer;
    transition: all 0.2s;
}

.option-item:hover, .option-item.active {
    background: var(--builder-bg-primary);
    color: var(--builder-accent);
}

.input-spinners-inline {
    display: flex;
    flex-direction: column;
    height: 100%;
    border-left: 1px solid var(--builder-field-border-clean);
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
    transition: color 0.2s;
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

.slider-wrapper {
    margin-top: 4px;
}
</style>
