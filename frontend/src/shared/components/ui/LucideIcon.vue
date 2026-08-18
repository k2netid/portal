<template>
  <component 
    :is="iconComponent" 
    v-if="iconComponent" 
    :size="numericSize" 
    :stroke-width="strokeWidth" 
    :color="color" 
    :class="props.class" 
  />
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { computed, type HTMLAttributes, type Component } from 'vue'
import * as LucideIcons from 'lucide-vue-next';

const props = withDefaults(defineProps<{
  name: string;
  size?: number | string;
  strokeWidth?: number | string;
  color?: string;
  class?: HTMLAttributes['class'];
}>(), {
  size: '16',
  strokeWidth: '2',
  color: 'currentColor',
  class: '',
});

const numericSize = computed(() => {
    if (typeof props.size === 'string') {
        const parsed = parseInt(props.size);
        return isNaN(parsed) ? 16 : parsed;
    }
    return props.size;
})

// Convert to PascalCase for exports
const toPascalCase = (str: string): string => {
  return str.replace(/(^|[-_])(\w)/g, (_, __, c) => c.toUpperCase());
};

// Common icon aliases for backward compatibility (Renamed in Lucide v0.4xx+)
const ICON_ALIASES: Record<string, string> = {
    'HelpCircle': 'CircleHelp',
    'AlertCircle': 'CircleAlert',
    'PlusCircle': 'CirclePlus',
    'XCircle': 'CircleX',
    'CheckCircle2': 'CircleCheckBig',
    'Circle2': 'Circle',
    'MoreHorizontal': 'Ellipsis',
    'MoreVertical': 'EllipsisVertical',
    'ArrowUpCircle': 'CircleArrowUp',
    'ArrowDownCircle': 'CircleArrowDown',
    'ArrowLeftCircle': 'CircleArrowLeft',
    'ArrowRightCircle': 'CircleArrowRight',
    'Edit3': 'PenTool',
    'Edit': 'Pen',
    'Filter': 'ListFilter',
    'Sort': 'ArrowUpDown',
    'Grid': 'Grid2X2',
    'Layout': 'LayoutDashboard'
};

const iconComponent = computed<Component | null>(() => {
  const name = props.name;
  if (!name) return null;
  let targetName = toPascalCase(name);
  if (ICON_ALIASES[targetName]) {
    targetName = ICON_ALIASES[targetName] || targetName;
  }
  const icon = (LucideIcons as unknown as Record<string, Component | undefined>)[targetName] ?? null;
  if (!icon) {
    logger.warning(`LucideIcon: Icon "${name}" not found in lucide-vue-next`);
  }
  return icon;
});
</script>

