<template>
  <div class="icon-field">
    <!-- Inline Picker Header -->
    <div class="icon-field-controls">
      <BaseInput 
        v-model="searchQuery" 
        :placeholder="$t('builder.fields.icon.search')"
        size="sm"
        class="search-input"
      >
        <template #prefix>
          <Search :size="14" />
        </template>
      </BaseInput>
      
      <BaseButton variant="secondary" size="sm" @click="toggleFilter" class="filter-btn">
        <Plus :size="14" />
        {{ $t('builder.fields.icon.filter') }}
      </BaseButton>

      <BaseButton variant="ghost" size="sm" @click="openModal" class="expand-btn">
        <Maximize2 :size="14" />
      </BaseButton>
    </div>

    <!-- Icon Grid (5x5 or similar) -->
    <div class="icon-grid">
      <button 
        v-for="iconName in filteredIcons" 
        :key="iconName"
        class="icon-item"
        :class="{ 'icon-item--active': activeValue === iconName }"
        @click="selectIcon(iconName)"
        :title="iconName"
      >
        <LucideIcon :name="iconName" :size="18" />
      </button>
    </div>

    <!-- Empty State -->
    <div v-if="filteredIcons.length === 0" class="empty-state">
      {{ $t('builder.fields.icon.noResults') }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, inject } from 'vue'
import type { BuilderInstance, SettingDefinition } from '@/types/builder'
import Search from 'lucide-vue-next/dist/esm/icons/search.js';
import Plus from 'lucide-vue-next/dist/esm/icons/plus.js';
import Maximize2 from 'lucide-vue-next/dist/esm/icons/maximize.js';
import { BaseInput, BaseButton } from '@/components/builder/ui'
import { LucideIcon } from '@/components/ui';
import { commonIcons, allIcons } from '@/shared/assets/icons'

const props = defineProps<{
  field?: SettingDefinition;
  value?: string;
  placeholderValue?: string | null;
}>()

const emit = defineEmits(['update:value'])

const builder = inject<BuilderInstance>('builder')
const searchQuery = ref('')

const activeValue = computed(() => props.value || props.placeholderValue)

const filteredIcons = computed(() => {
  if (!searchQuery.value) {
    // If we have a value that's NOT in commonIcons, prepend it
    const list = [...commonIcons]
    if (activeValue.value && !list.includes(activeValue.value)) {
      list.unshift(activeValue.value)
    }
    return list.slice(0, 25) // 5x5 grid
  }
  
  const query = searchQuery.value.toLowerCase()
  return allIcons.filter(name => 
    name.toLowerCase().includes(query)
  ).slice(0, 25)
})

const selectIcon = (iconName: string) => {
  emit('update:value', iconName)
}

const openModal = () => {
  builder?.openIconPickerModal?.(activeValue.value || '', (iconName: string) => {
    selectIcon(iconName)
  })
}

const toggleFilter = () => {
  // Opening the full modal is essentially the "filter" logic for now
  openModal()
}
</script>

<style scoped>
.icon-field {
  display: flex;
  flex-direction: column;
  gap: 8px;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: 6px;
  padding: 8px;
}

.icon-field-controls {
  display: flex;
  gap: 4px;
  align-items: center;
}

.search-input {
  flex: 1;
}

.filter-btn {
  white-space: nowrap;
  gap: 4px;
  padding: 0 8px;
  height: 32px;
  font-size: 11px;
}

.expand-btn {
  color: var(--builder-text-secondary);
  padding: 8px;
  border-radius: 4px;
  height: 32px;
  width: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.expand-btn:hover {
  background: var(--builder-bg-primary);
  color: var(--builder-accent);
}

.icon-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 4px;
  padding: 2px;
}

.icon-item {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--builder-bg-primary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  color: var(--builder-text-primary);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.icon-item:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
  color: var(--builder-accent);
  transform: scale(1.05);
}

.icon-item--active {
  background: var(--builder-accent-soft);
  border-color: var(--builder-accent);
  color: var(--builder-accent);
  box-shadow: inset 0 0 0 1px var(--builder-accent);
}

.empty-state {
  padding: 12px;
  text-align: center;
  color: var(--builder-text-muted);
  font-size: 12px;
  font-style: italic;
}
</style>
