<template>
  <div class="scroll-effects-field">
    <div class="scroll-tabs">
      <button 
        v-for="tab in tabs" 
        :key="tab.id"
        class="scroll-tab-btn"
        :class="{ active: activeTab === tab.id, enabled: isTabEnabled(tab.id) }"
        @click="activeTab = tab.id"
        :title="tab.label"
      >
        <component :is="tab.icon" :size="16" />
      </button>
    </div>

    <div class="tab-content mt-4">
      <div class="flex items-center justify-between mb-4">
        <BaseLabel class="mb-0">{{ activeTabLabel }}</BaseLabel>
        <BaseToggle 
          v-model="localValue[activeTab].enabled" 
          @update:model-value="updateValue"
        />
      </div>

      <div v-if="localValue[activeTab].enabled" class="effect-controls space-y-4">
        <!-- Placeholder for actual motion controls like range sliders, etc. -->
        <!-- In a real implementation, each motion type would have its own set of sliders (Entrance, Middle, Exit) -->
        <div class="info-box text-xs">
          {{ $t('builder.advanced.scroll.configure', { type: activeTabLabel }) }}
        </div>
        
        <div class="grid grid-cols-1 gap-4">
           <!-- Dynamic controls would go here based on activeTab -->
           <div class="flex flex-col gap-2">
             <BaseLabel class="text-[10px]">{{ $t('builder.advanced.scroll.level', 'Intensity') }}</BaseLabel>
             <input type="range" class="builder-range" v-model="localValue[activeTab].level" @input="updateValue">
           </div>
        </div>
      </div>
      <div v-else class="text-center py-4 text-xs text-muted italic">
        {{ $t('builder.advanced.scroll.tabDisabled', { type: activeTabLabel }) }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue'
import type { BlockInstance } from '../../../types/builder'
import ArrowUpDown from 'lucide-vue-next/dist/esm/icons/arrow-up-down.js';
import ArrowLeftRight from 'lucide-vue-next/dist/esm/icons/arrow-left-right.js';
import Scaling from 'lucide-vue-next/dist/esm/icons/scaling.js';
import RotateCw from 'lucide-vue-next/dist/esm/icons/rotate-cw.js';
import Droplet from 'lucide-vue-next/dist/esm/icons/droplet.js';
import Layers from 'lucide-vue-next/dist/esm/icons/layers.js';
import { BaseLabel, BaseToggle } from '../ui'
import type { SettingDefinition } from '@/types/builder'

interface ScrollEffectState {
  enabled: boolean;
  level: number;
}

const props = defineProps<{
  field: SettingDefinition;
  value: Record<string, ScrollEffectState>;
  module: BlockInstance;
}>()

const emit = defineEmits(['update:value'])

const activeTab = ref('vertical')

const tabs = [
  { id: 'vertical', label: 'Vertical Motion', icon: ArrowUpDown },
  { id: 'horizontal', label: 'Horizontal Motion', icon: ArrowLeftRight },
  { id: 'scaling', label: 'Scaling', icon: Scaling },
  { id: 'rotating', label: 'Rotating', icon: RotateCw },
  { id: 'opacity', label: 'Opacity', icon: Layers },
  { id: 'blur', label: 'Blur', icon: Droplet }
]

const localValue = reactive<Record<string, ScrollEffectState>>({
  vertical: { enabled: false, level: 0 },
  horizontal: { enabled: false, level: 0 },
  scaling: { enabled: false, level: 0 },
  rotating: { enabled: false, level: 0 },
  opacity: { enabled: false, level: 100 },
  blur: { enabled: false, level: 0 },
  ...props.value
})

const activeTabLabel = computed(() => {
  return tabs.find(t => t.id === activeTab.value)?.label
})

const isTabEnabled = (tabId: string) => {
  return localValue[tabId]?.enabled
}

const updateValue = () => {
  emit('update:value', { ...localValue })
}

watch(() => props.value, (newVal) => {
  Object.assign(localValue, newVal)
}, { deep: true })

</script>

<style scoped>
.scroll-effects-field {
  width: 100%;
}

.scroll-tabs {
  display: flex;
  background: var(--builder-bg-secondary);
  border: 1px solid var(--builder-border);
  border-radius: var(--radius-md);
  padding: 2px;
  gap: 2px;
}

.scroll-tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 32px;
  border: none;
  background: transparent;
  color: var(--builder-text-muted);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s;
}

.scroll-tab-btn:hover {
  background: var(--builder-bg-primary);
  color: var(--builder-text-primary);
}

.scroll-tab-btn.active {
  background: var(--builder-bg-primary);
  color: var(--builder-accent);
  box-shadow: var(--shadow-sm);
}

.scroll-tab-btn.enabled {
  color: var(--builder-accent);
}

.info-box {
  background: rgba(59, 130, 246, 0.05);
  border-left: 2px solid var(--builder-accent);
  padding: 8px var(--spacing-md);
  color: var(--builder-text-muted);
}

.builder-range {
  width: 100%;
  accent-color: var(--builder-accent);
}

.mt-4 {
  margin-top: 1rem;
}
</style>
