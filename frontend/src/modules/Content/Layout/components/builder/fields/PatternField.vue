<template>
  <div class="pattern-field">
    <div class="patterns-grid">
      <div 
        v-for="pattern in BackgroundPatterns" 
        :key="pattern.id"
        class="pattern-item"
        :class="{ 'is-selected': value === pattern.id }"
        @click="$emit('update:value', pattern.id)"
        :title="t('builder.fields.background.pattern.options.' + pattern.id)"
      >
        <div class="pattern-preview">
          <svg 
            :viewBox="`0 0 ${pattern.width} ${pattern.height}`" 
            xmlns="http://www.w3.org/2000/svg"
            class="pattern-svg"
          >
            <rect width="100%" height="100%" fill="transparent" />
            <g v-html="pattern.svg.regular?.default || pattern.svg.default || pattern.svg" fill="currentColor" stroke="currentColor"></g>
          </svg>
        </div>
      </div>
      
      <!-- None Option -->
      <div 
        class="pattern-item is-none"
        :class="{ 'is-selected': !value || value === 'none' }"
        @click="$emit('update:value', 'none')"
        :title="t('builder.common.none')"
      >
        <div class="pattern-preview">
          <Ban :size="20" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Ban from 'lucide-vue-next/dist/esm/icons/ban.js';
import { useI18n } from 'vue-i18n'
import { BackgroundPatterns } from '@/shared/utils/AssetLibrary'
import type { SettingDefinition } from '@/types/builder'

defineProps<{
  field?: SettingDefinition;
  value?: string;
}>()

defineEmits(['update:value'])
const { t } = useI18n()
</script>

<style scoped>
.pattern-field {
  padding: 4px;
}

.patterns-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}

.pattern-item {
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

.pattern-item:hover {
  border-color: var(--builder-accent);
  background: var(--builder-bg-secondary);
}

.pattern-item.is-selected {
  border-color: var(--builder-accent);
  background: var(--builder-accent-soft, rgba(37, 99, 235, 0.1));
  box-shadow: 0 0 0 1px var(--builder-accent);
}

.pattern-preview {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--builder-text-primary);
}

.pattern-svg {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.is-none {
  color: var(--builder-text-muted);
}
</style>
