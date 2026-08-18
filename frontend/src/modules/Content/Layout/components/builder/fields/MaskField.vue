<template>
  <div class="mask-field">
    <div class="masks-grid">
      <div 
        v-for="mask in BackgroundMasks" 
        :key="mask.id"
        class="mask-item"
        :class="{ 'is-selected': value === mask.id }"
        @click="$emit('update:value', mask.id)"
        :title="mask.label"
      >
        <div class="mask-preview">
          <svg 
            :viewBox="mask.viewBox.square" 
            xmlns="http://www.w3.org/2000/svg"
            class="mask-svg"
          >
            <rect width="100%" height="100%" fill="var(--builder-bg-tertiary)" />
            <g v-html="getMaskPreview(mask)" fill="currentColor"></g>
          </svg>
        </div>
      </div>
      
      <!-- None Option -->
      <div 
        class="mask-item is-none"
        :class="{ 'is-selected': !value || value === 'none' }"
        @click="$emit('update:value', 'none')"
        :title="t('builder.common.none')"
      >
        <div class="mask-preview">
          <Ban :size="20" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Ban from 'lucide-vue-next/dist/esm/icons/ban.js';
import { useI18n } from 'vue-i18n'
import { BackgroundMasks } from '@/shared/utils/AssetLibrary'
import type { SettingDefinition } from '@/types/builder'

defineProps<{
  field?: SettingDefinition;
  value?: string;
}>()

defineEmits(['update:value'])
interface MaskDefinition {
  id: string;
  label: string;
  viewBox: { square: string; landscape: string; portrait: string };
  svg: unknown; // SVG structure is complex and varies
}

const { t } = useI18n()

const getMaskPreview = (mask: MaskDefinition) => {
    if (!mask || !mask.svg) return ''
    const svg = mask.svg as Record<string, unknown>
    // Check regular structure first
    const stateObj = (svg.regular || svg.default || svg) as Record<string, unknown>
    const rotateObj = (stateObj.default || stateObj) as Record<string, unknown> | string
    
    let path = ''
    if (typeof rotateObj === 'string') {
        path = rotateObj
    } else {
        path = String(rotateObj.square || rotateObj.landscape || rotateObj.portrait || '')
    }

    // Ensure currentColor is visible in preview
    return path.replace(/currentColor/g, 'var(--builder-text-primary)')
}
</script>

<style scoped>
.mask-field {
  padding: 4px;
}

.masks-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}

.mask-item {
  aspect-ratio: 1;
  background: var(--builder-bg-tertiary);
  border: 1px solid var(--builder-border);
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  overflow: hidden;
  padding: 4px;
}

.mask-item:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
}

.mask-item.is-selected {
  border-color: var(--builder-accent);
  background: var(--builder-accent-soft, rgba(37, 99, 235, 0.1));
  box-shadow: 0 0 0 1px var(--builder-accent);
}

.mask-preview {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--builder-text-primary);
}

.mask-svg {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.is-none {
  color: var(--builder-text-muted);
}
</style>
