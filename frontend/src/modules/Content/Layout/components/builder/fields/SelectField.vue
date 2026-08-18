<template>
  <div class="select-field-container">
    <BaseDropdown align="left" width="100%" ref="dropdownRef" no-padding>
      <template #trigger="{ open, toggle }">
        <div 
          class="select-trigger" 
          :class="{ 'is-open': open, 'is-placeholder': !hasValue && placeholderValue !== null, 'is-multiple': multiple }"
          @click="toggle"
        >
          <div class="trigger-content">
            <template v-if="multiple && Array.isArray(value) && value.length">
              <div class="selected-tags">
                <span 
                  v-for="val in (value as (string | number)[])" 
                  :key="val" 
                  class="select-tag"
                  @click.stop="removeValue(val)"
                >
                  {{ getLabelForValue(val) }}
                  <X :size="12" class="tag-remove" />
                </span>
              </div>
            </template>
            <span v-else class="selected-label">{{ translatedSelectedLabel }}</span>
          </div>
          <ChevronDown :size="14" class="select-arrow" />
        </div>
      </template>

      <template #default="{ close }">
        <div class="select-dropdown-content">
          <div v-if="searchable" class="search-container" @click.stop>
            <div class="search-input-wrapper">
              <Search :size="14" class="search-icon" />
              <input 
                ref="searchInput"
                v-model="searchQuery" 
                type="text" 
                :placeholder="t('builder.common.search') || 'Search...'"
                class="search-input"
              >
            </div>
          </div>
          
          <div class="options-list">
            <!-- Grouped Layout -->
            <template v-if="groupedOptions">
                <div v-for="(groupOpts, groupName) in groupedOptions" :key="groupName" class="option-group">
                    <div class="group-header">{{ getGroupLabel(groupName) }}</div>
                    <div 
                      v-for="option in groupOpts" 
                      :key="String(option.value)"
                      class="dropdown-item"
                      :class="{ 'active': isSelected(option.value) }"
                      @click.stop="handleSelect(option, close)"
                    >
                      <span>{{ getOptionLabel(option) }}</span>
                      <Check v-if="isSelected(option.value)" :size="14" class="check-icon" />
                    </div>
                </div>
            </template>

            <!-- Flat Layout -->
            <template v-else>
                <div 
                  v-for="option in filteredOptions" 
                  :key="String(option.value)"
                  class="dropdown-item"
                  :class="{ 'active': isSelected(option.value) }"
                  @click.stop="handleSelect(option, close)"
                >
                  <span>{{ getOptionLabel(option) }}</span>
                  <Check v-if="isSelected(option.value)" :size="14" class="check-icon" />
                </div>
            </template>
            
            <div v-if="filteredOptions.length === 0" class="no-options">
              {{ t('builder.common.no_results') || 'No results found' }}
            </div>
          </div>
        </div>
      </template>
    </BaseDropdown>
  </div>
</template>

<script setup lang="ts">
import { computed, inject, ref, watch } from 'vue'
import type { BlockInstance, BuilderInstance, SettingDefinition, SettingOption } from '@/types/builder'
import { useI18n } from 'vue-i18n'
import ChevronDown from 'lucide-vue-next/dist/esm/icons/chevron-down.js';
import Check from 'lucide-vue-next/dist/esm/icons/check.js';
import X from 'lucide-vue-next/dist/esm/icons/x.js';
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import { BaseDropdown } from '@/components/builder/ui'

declare global {
  interface Window {
    jaCmsData?: Record<string, unknown>;
  }
}

const props = defineProps<{
  field: SettingDefinition;
  value: string | number | string[] | number[] | null | undefined;
  placeholderValue?: string | number | null;
  multiple?: boolean;
  searchable?: boolean;
}>()

const emit = defineEmits(['update:value'])

const { t, te } = useI18n()
const module = inject<BlockInstance>('module', {} as BlockInstance)
const dropdownRef = ref<{ isOpen: boolean } | null>(null)
const searchQuery = ref('')
const searchInput = ref<HTMLInputElement | null>(null)

// Initialize computed options from field definition
const rawOptions = computed(() => {
    const options = props.field.options

    // Check if options is a dynamic string identifier
    if (typeof options === 'string' && options.startsWith('dynamic:')) {
        const key = options.replace('dynamic:', '')
        const dynamicData = window.jaCmsData || {}
        return (dynamicData[key] || []) as SettingOption[]
    }

    // Default to static array
    return (Array.isArray(options) ? options : []) as SettingOption[]
})

const builder = inject<BuilderInstance>('builder')

const filteredOptions = computed(() => {
  let opts = rawOptions.value

  // 1. Dependency Filtering
  if (props.field.filter_by && module && module.id && builder) {
      const filterConfig = props.field.filter_by
      const settings = builder.findModule(module.id)?.settings
      const depValue = settings?.[filterConfig.field]

      if (depValue) {
          opts = opts.filter((opt) => {
              const optValue = (opt as unknown as Record<string, unknown>)[filterConfig.match_key]
              // Handle array dependency (e.g. post_type=['post','page'])
              if (Array.isArray(depValue)) {
                  return depValue.includes(optValue)
              }
              return depValue === optValue
          })
      }
  }

  // 2. Search Filtering
  if (props.searchable && searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    opts = opts.filter((opt) => {
      const label = getOptionLabel(opt).toLowerCase()
      return label.includes(query)
    })
  }

  return opts
})

// Grouping Logic
const groupedOptions = computed(() => {
    // Check if options have 'taxonomy' or 'group' property
    const sample = filteredOptions.value[0]
    const groupKey = sample && 'taxonomy' in sample ? 'taxonomy' : (sample && 'group' in sample ? 'group' : null)

    if (!groupKey) return null

    // Group options
    const groups: Record<string, SettingOption[]> = {}
    filteredOptions.value.forEach((opt) => {
        const key = String((opt as unknown as Record<string, unknown>)[groupKey] || 'Other')
        if (!groups[key]) groups[key] = []
        groups[key].push(opt)
    })
    return groups
})

const getGroupLabel = (key: string) => {
    // Map taxonomy keys to readable labels if needed
    const map: Record<string, string> = {
        'category': 'Categories',
        'post_tag': 'Tags',
        'post_format': 'Formats',
        'project_category': 'Project Categories',
        'project_tag': 'Project Tags'
    }
    return map[key] || key.charAt(0).toUpperCase() + key.slice(1)
}


const hasValue = computed(() => {
  if (props.multiple) {
    return Array.isArray(props.value) && props.value.length > 0
  }
  return props.value !== '' && props.value !== null && props.value !== undefined
})

const getOptionLabel = (option: SettingOption | string | number) => {
  // If option is just a string (simple array of strings), return it
  if (typeof option !== 'object' || option === null) return String(option)
    
  const type = module.type
  const name = props.field.key || props.field.name || ''
  const val = option.value

  // 1. Module specific
  if (te(`builder.settings.${type}.${name}.options.${val}`)) {
    return t(`builder.settings.${type}.${name}.options.${val}`)
  }

  // 2. Common field
  if (te(`builder.settings.fields.${name}.options.${val}`)) {
    return t(`builder.settings.fields.${name}.options.${val}`)
  }

  return option.label || String(val)
}

const getLabelForValue = (val: string | number) => {
    const option = rawOptions.value.find((o) => o.value === val)
    return option ? getOptionLabel(option) : String(val)
}

const translatedSelectedLabel = computed(() => {
  if (!hasValue.value) {
      if (props.placeholderValue !== null && props.placeholderValue !== undefined) {
         const inherited = rawOptions.value.find((opt) => opt.value === props.placeholderValue)
         if (inherited) return getOptionLabel(inherited)
      }
      return t('builder.common.select') || 'Select...'
  }

  // For single select
  return getLabelForValue(props.value as string | number)
})

const isSelected = (val: string | number | boolean | null) => {
  if (props.multiple) {
    return Array.isArray(props.value) && (props.value as (string | number | boolean | null)[]).includes(val)
  }
  return props.value === val
}

const handleSelect = (option: SettingOption, close: () => void) => {
  if (props.multiple) {
    let newValue = Array.isArray(props.value) ? [...props.value] : []
    const index = (newValue as (string | number | boolean | null)[]).indexOf(option.value);
    
    if (index === -1) {
      (newValue as (string | number | boolean | null)[]).push(option.value)
    } else {
      newValue.splice(index, 1)
    }
    
    emit('update:value', newValue)
    // Don't close dropdown for multiple select
  } else {
    emit('update:value', option.value)
    close()
  }
}

const removeValue = (val: string | number) => {
    if (!props.multiple) return
    let newValue = Array.isArray(props.value) ? [...props.value] : []
    const index = (newValue as (string | number | boolean | null)[]).indexOf(val)
    if (index !== -1) {
        newValue.splice(index, 1)
        emit('update:value', newValue)
    }
}

// Focus search input when dropdown opens
watch(() => dropdownRef.value?.isOpen, (isOpen) => {
    if (isOpen && props.searchable) {
        setTimeout(() => {
            searchInput.value?.focus()
        }, 50)
    } else {
        searchQuery.value = '' // Reset search on close
    }
})
</script>

<style scoped>
.select-field-container {
  width: 100%;
}

:deep(.base-dropdown-wrapper) {
  width: 100%;
  display: block; /* Ensure it takes full width */
}

.select-trigger {
  background-color: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--border-radius-md);
  padding: 8px 12px;
  min-height: 36px;
  font-size: var(--font-size-md);
  color: var(--builder-text-primary);
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.2s;
  width: 100%;
}

.select-trigger.is-multiple {
  padding: 4px 8px; /* Slightly tighter padding for tags */
}

.select-trigger.is-placeholder {
  color: var(--builder-text-muted);
}

.select-trigger:hover, .select-trigger.is-open {
  border-color: var(--builder-accent);
  background-color: var(--builder-bg-primary);
}

.trigger-content {
    flex: 1;
    overflow: hidden;
    margin-right: 8px;
}

.selected-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.select-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background-color: var(--builder-bg-tertiary); /* Darker/Lighter background */
    border: 1px solid var(--builder-border);
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 12px;
    color: var(--builder-text-primary);
    line-height: 1.4;
}

.tag-remove {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
}

.tag-remove:hover {
    opacity: 1;
    color: var(--builder-error);
}

.select-arrow {
  color: var(--builder-text-muted);
  flex-shrink: 0;
}

.select-dropdown-content {
    display: flex;
    flex-direction: column;
    max-height: 300px;
}

.search-container {
    padding: 10px;
    background-color: var(--builder-bg-primary);
    border-bottom: 1px solid var(--builder-border);
    position: sticky;
    top: 0;
    z-index: 1;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 10px;
    color: var(--builder-text-muted);
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 7px 10px 7px 32px;
    background-color: var(--builder-bg-secondary);
    border: 1px solid var(--builder-border);
    border-radius: 6px;
    color: var(--builder-text-primary);
    font-size: 13px;
    outline: none;
    transition: var(--transition-fast);
}

.search-input:focus {
    border-color: var(--builder-accent);
    background-color: var(--builder-bg-primary);
    box-shadow: 0 0 0 2px rgba(var(--builder-accent-rgb), 0.1);
}

.options-list {
    overflow-y: auto;
    padding: 4px;
}

.dropdown-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
    color: var(--builder-text-secondary);
    font-size: 13px;
    transition: background-color 0.15s;
}

.dropdown-item:hover {
    background-color: var(--builder-bg-tertiary);
    color: var(--builder-text-primary);
}

.dropdown-item.active {
    background-color: var(--builder-accent);
    color: white;
}

.check-icon {
  margin-left: 8px;
}

/* Grouping Styles */
.option-group {
    margin-bottom: 4px;
}

.group-header {
    padding: 8px 12px 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--builder-text-muted);
    pointer-events: none;
}

.option-group .dropdown-item {
    padding-left: 16px;
}

.no-options {
    padding: 12px;
    text-align: center;
    color: var(--builder-text-muted);
    font-size: 13px;
}
</style>
