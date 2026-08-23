<template>
  <div class="module-actions">
    <!-- Settings (Edit) -->
    <div v-if="showEdit" class="action-icon" :title="t('builder.items.settings')" @click.stop="$emit('edit')">
        <Settings :size="size" />
    </div>

    <!-- Duplicate -->
    <div v-if="showDuplicate" class="action-icon" :title="t('builder.items.duplicate')" @click.stop="$emit('duplicate')">
        <Copy :size="size" />
    </div>

    <!-- Layout (Rows) -->
    <div v-if="showLayout" class="action-icon" :title="t('builder.items.layout')" @click.stop="$emit('layout')">
        <Columns :size="size" />
    </div>

    <!-- Delete -->
    <div v-if="showDelete" class="action-icon delete-btn" :title="t('builder.items.delete')" @click.stop="$emit('delete')">
        <Trash :size="size" />
    </div>

    <!-- More (Meatballs) -->
    <div v-if="showMore" class="action-icon" :title="t('builder.items.more')" @click.stop="$emit('more', $event)">
        <MoreVertical :size="size" />
    </div>
    
    <!-- Drag Handle -->
    <div v-if="showDrag" class="action-icon drag-handle" :title="t('builder.items.drag')">
        <GripVertical :size="size" />
    </div>
  </div>
</template>

<script setup lang="ts">

import { useI18n } from 'vue-i18n'
import Settings from 'lucide-vue-next/dist/esm/icons/settings.js';
import Copy from 'lucide-vue-next/dist/esm/icons/copy.js';
import Trash from 'lucide-vue-next/dist/esm/icons/trash.js';
import GripVertical from 'lucide-vue-next/dist/esm/icons/grip-vertical.js';
import Columns from 'lucide-vue-next/dist/esm/icons/columns-2.js';
import MoreVertical from 'lucide-vue-next/dist/esm/icons/ellipsis-vertical.js';

const { t } = useI18n()

interface Props {
  label?: string;
  size?: number;
  showEdit?: boolean;
  showLayout?: boolean;
  showDuplicate?: boolean;
  showDelete?: boolean;
  showDrag?: boolean;
  showMore?: boolean;
}

withDefaults(defineProps<Props>(), {
  label: 'Item',
  size: 14,
  showEdit: true,
  showLayout: false,
  showDuplicate: true,
  showDelete: true,
  showDrag: false,
  showMore: true
})

defineEmits<{
  (e: 'edit'): void;
  (e: 'layout'): void;
  (e: 'duplicate'): void;
  (e: 'delete'): void;
  (e: 'more', event: MouseEvent): void;
}>()
</script>

<style scoped>
.module-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.action-icon {
    color: var(--builder-text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    transition: all 0.15s;
    border-radius: 4px;
}

.action-icon:hover,
.action-icon.is-active {
    color: var(--builder-accent);
    background: rgba(var(--builder-accent-rgb), 0.1);
}

.delete-btn:hover {
    color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.1) !important;
}

/* Dropdown Menu - Styled similar to FieldActions */
.context-menu {
    display: flex;
    flex-direction: column;
}

.menu-item {
    padding: 8px 12px;
    font-size: 12px;
    color: var(--builder-text-primary);
    cursor: pointer;
    transition: background-color 0.15s;
}

.menu-item:hover {
    background-color: var(--builder-bg-secondary);
}

.menu-item.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.menu-divider {
    height: 1px;
    background-color: var(--builder-border);
    margin: 4px 0;
}
</style>
