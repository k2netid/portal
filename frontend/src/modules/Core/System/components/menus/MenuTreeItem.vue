<template>
  <div class="space-y-1.5 select-none">
    <!-- Main Menu Item Card -->
    <div
      :draggable="true"
      :class="[
        'flex items-center justify-between p-3 rounded-xl border transition-all duration-150 bg-card hover:bg-muted/20 group relative',
        isDragging ? 'opacity-40 scale-95' : '',
        isDropTarget ? 'ring-2 ring-primary bg-primary/10 border-primary/40' : 'border-border/60 shadow-xs'
      ]"
      @dragstart="onDragStart"
      @dragover.prevent="onDragOver"
      @dragleave="onDragLeave"
      @drop.prevent="onDrop"
      @dragend="onDragEnd"
    >
      <!-- Left: Drag Grip, Expand Caret, Icon, and Info -->
      <div class="flex items-center gap-2.5 min-w-0 flex-1">
        <!-- Drag Grip Handle -->
        <GripVertical class="w-4 h-4 text-muted-foreground/50 group-hover:text-foreground cursor-grab shrink-0 transition-colors" />

        <!-- Caret Expand for parent items -->
        <button
          v-if="hasChildren"
          type="button"
          class="p-0.5 text-muted-foreground hover:text-foreground rounded transition-transform"
          @click.stop="isExpanded = !isExpanded"
        >
          <ChevronRight :class="['w-4 h-4 transition-transform duration-200', isExpanded ? 'rotate-90' : '']" />
        </button>
        <div v-else class="w-4" />

        <!-- Menu Icon -->
        <div class="w-8 h-8 rounded-lg bg-muted/40 border border-border/40 flex items-center justify-center text-primary shrink-0">
          <LucideIcon :name="menu.icon || 'circle'" class="w-4 h-4" />
        </div>

        <!-- Name & Route Details -->
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-foreground truncate">{{ menu.name }}</span>

            <!-- Badge Preview -->
            <span
              v-if="menu.badge_text"
              :class="[
                'px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider',
                getBadgeClass(menu.badge_variant)
              ]"
            >
              {{ menu.badge_text }}
            </span>

            <!-- Extension Binding Tag -->
            <span
              v-if="menu.extension_slug"
              class="px-1.5 py-0.2 rounded bg-muted text-[9px] font-semibold text-muted-foreground border border-border/40"
              :title="'Bound to extension: ' + menu.extension_slug"
            >
              ext: {{ menu.extension_slug }}
            </span>
            <span
              v-if="isModuleInactive"
              class="px-1.5 py-0.2 rounded bg-amber-500/15 text-[9px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 border border-amber-500/30"
            >
              inactive module
            </span>
          </div>

          <div class="flex items-center gap-2 text-[10px] text-muted-foreground mt-0.5 flex-wrap">
            <span v-if="menu.route_name" class="font-mono">route: {{ menu.route_name }}</span>
            <span v-else-if="menu.url" class="font-mono">url: {{ menu.url }}</span>
            <span v-else class="italic opacity-60">Group Header</span>

            <span v-if="menu.permission" class="text-primary/80 font-medium">
              • perm: {{ menu.permission }}
            </span>
            <span v-if="menu.role" class="text-amber-500 font-medium">
              • role: {{ menu.role }}
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Visibility Switch, Edit & Delete Buttons -->
      <div class="flex items-center gap-1.5 shrink-0 ml-2">
        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
          title="Edit menu item"
          @click="$emit('edit', menu)"
        >
          <Edit2 class="w-3.5 h-3.5" />
        </Button>

        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-destructive rounded-lg"
          title="Delete menu item"
          @click="$emit('delete', menu.id)"
        >
          <Trash2 class="w-3.5 h-3.5" />
        </Button>
      </div>
    </div>

    <!-- Nested Children Tree View -->
    <div
      v-if="hasChildren && isExpanded"
      class="pl-6 ml-4 border-l border-border/50 space-y-1.5 animate-in slide-in-from-left-2 duration-150"
    >
      <MenuTreeItem
        v-for="child in menu.children"
        :key="child.id"
        :menu="child"
        :dragged-id="draggedId"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
        @drag-start="$emit('drag-start', $event)"
        @drag-end="$emit('drag-end')"
        @drop-item="$emit('drop-item', $event)"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import {
  GripVertical,
  ChevronRight,
  Edit2,
  Trash2,
} from 'lucide-vue-next';
import { Button, LucideIcon } from '@/shared/components/ui';
import type { ConsoleMenuItem } from '@/modules/Core/System/composables/useConsoleMenu';

const props = defineProps<{
    menu: ConsoleMenuItem;
    draggedId?: string | null;
}>();

const emit = defineEmits<{
    (e: 'edit', menu: ConsoleMenuItem): void;
    (e: 'delete', id: string): void;
    (e: 'drag-start', id: string): void;
    (e: 'drag-end'): void;
    (e: 'drop-item', payload: { draggedId: string; targetId: string }): void;
}>();

const isExpanded = ref(true);
const isDropTarget = ref(false);
const systemStore = useSystemStore();

const isModuleInactive = computed(() => {
    const slug = props.menu.extension_slug;
    if (!slug) {
        return false;
    }
    return !systemStore.activeExtensions.includes(slug);
});

const hasChildren = computed(() => {
    return Array.isArray(props.menu.children) && props.menu.children.length > 0;
});

const isDragging = computed(() => {
    return props.draggedId === props.menu.id;
});

const getBadgeClass = (variant?: string) => {
    switch (variant) {
        case 'emerald':
            return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20';
        case 'amber':
            return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20';
        case 'rose':
            return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20';
        case 'primary':
        default:
            return 'bg-primary/10 text-primary border border-primary/20';
    }
};

const onDragStart = (e: DragEvent) => {
    emit('drag-start', props.menu.id);
    if (e.dataTransfer) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', props.menu.id);
    }
};

const onDragOver = (e: DragEvent) => {
    if (props.draggedId && props.draggedId !== props.menu.id) {
        isDropTarget.value = true;
        if (e.dataTransfer) {
            e.dataTransfer.dropEffect = 'move';
        }
    }
};

const onDragLeave = () => {
    isDropTarget.value = false;
};

const onDrop = () => {
    if (props.draggedId && props.draggedId !== props.menu.id) {
        emit('drop-item', { draggedId: props.draggedId, targetId: props.menu.id });
    }
    isDropTarget.value = false;
};

const onDragEnd = () => {
    isDropTarget.value = false;
    emit('drag-end');
};
</script>
