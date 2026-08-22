<template>
  <div
    :class="[
      'h-full flex flex-col justify-between p-2.5 select-none transition-all duration-300 relative bg-card/30',
      isMinimized ? 'w-16 items-center' : 'w-60'
    ]"
  >
    <!-- Navigation Scroll Container -->
    <div class="flex-1 w-full overflow-y-auto overflow-x-hidden custom-scrollbar space-y-3 pr-1 min-h-0">
      <!-- Main Folders Section (Collapsible Accordion) -->
      <div class="space-y-0.5">
        <!-- Folders Accordion Header -->
        <div
          v-if="!isMinimized"
          class="flex items-center justify-between px-2 py-1 mb-1 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70 cursor-pointer hover:text-foreground group rounded transition-colors"
          @click="isFoldersOpen = !isFoldersOpen"
        >
          <div class="flex items-center gap-1.5">
            <ChevronDown
              :class="[
                'w-3 h-3 transition-transform duration-200 text-muted-foreground group-hover:text-foreground',
                !isFoldersOpen ? '-rotate-90' : ''
              ]"
            />
            <span>{{ $t('system.mail.folders') }}</span>
          </div>
          <span class="text-[9px] font-normal text-muted-foreground/60">
            {{ isFoldersOpen ? 'Collapse' : 'Expand' }}
          </span>
        </div>

        <!-- Folders List Container -->
        <div v-show="isMinimized || isFoldersOpen" class="space-y-0.5 animate-in fade-in-50 duration-150">
          <button
            v-for="folder in folderList"
            :key="folder.id"
            :class="[
              'w-full flex items-center rounded-lg text-xs font-medium transition-all group relative',
              isMinimized ? 'justify-center p-2.5' : 'justify-between px-3 py-2',
              activeFolder === folder.id && !activeLabel
                ? 'bg-primary/10 text-primary font-semibold'
                : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.99]'
            ]"
            :title="isMinimized ? $t(`system.mail.folder_${folder.id}`) : undefined"
            @click="selectFolder(folder.id)"
          >
            <div class="flex items-center gap-2.5">
              <component
                :is="folder.icon"
                class="w-4 h-4 shrink-0 opacity-80 group-hover:opacity-100"
              />
              <span v-if="!isMinimized" class="truncate">{{ $t(`system.mail.folder_${folder.id}`) }}</span>
            </div>

            <!-- Badge Unread -->
            <template v-if="(folderCounts?.[folder.id] ?? 0) > 0">
              <span
                v-if="!isMinimized"
                :class="[
                  'px-1.5 py-0.5 rounded text-[10px] font-bold',
                  activeFolder === folder.id && !activeLabel
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-muted text-muted-foreground'
                ]"
              >
                {{ folderCounts?.[folder.id] }}
              </span>
              <span
                v-else
                class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-primary"
              />
            </template>
          </button>
        </div>
      </div>

      <!-- Labels Section (Collapsible Accordion + Tree Hierarchy + Drag and Drop) -->
      <div class="space-y-0.5 pt-2 border-t border-border/40">
        <!-- Labels Accordion Header & Top-Level Drop Target -->
        <div
          v-if="!isMinimized"
          :class="[
            'flex items-center justify-between px-2 py-1 mb-1 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70 rounded transition-colors',
            isHeaderDropHover ? 'bg-primary/15 text-primary ring-1 ring-primary/40' : 'hover:text-foreground'
          ]"
          @dragover.prevent="handleHeaderDragOver"
          @dragleave="handleHeaderDragLeave"
          @drop.prevent="handleHeaderDrop"
        >
          <div
            class="flex items-center gap-1.5 cursor-pointer select-none group flex-1"
            @click="isLabelsOpen = !isLabelsOpen"
          >
            <ChevronDown
              :class="[
                'w-3 h-3 transition-transform duration-200 text-muted-foreground group-hover:text-foreground',
                !isLabelsOpen ? '-rotate-90' : ''
              ]"
            />
            <span>{{ $t('system.mail.labels') }}</span>
            <span v-if="labels.length > 0" class="text-[9px] font-normal text-muted-foreground/60">
              ({{ labels.length }})
            </span>
          </div>

          <button
            type="button"
            class="text-muted-foreground hover:text-primary p-0.5 rounded transition-colors"
            :title="$t('system.mail.manage_labels')"
            @click.stop="$emit('manage-labels')"
          >
            <Plus class="w-3.5 h-3.5" />
          </button>
        </div>

        <button
          v-else
          type="button"
          class="w-full flex justify-center p-2 text-muted-foreground hover:text-primary"
          :title="$t('system.mail.manage_labels')"
          @click.stop="$emit('manage-labels')"
        >
          <Plus class="w-3.5 h-3.5" />
        </button>

        <!-- Labels Tree View Container -->
        <div v-show="isMinimized || isLabelsOpen" class="space-y-0.5 animate-in fade-in-50 duration-150">
          <div
            v-for="label in rootLabels"
            :key="label.id"
            class="space-y-0.5"
          >
            <!-- Parent Label Item -->
            <div
              :draggable="!isMinimized"
              :class="[
                'w-full flex items-center rounded-lg text-xs font-medium transition-all group relative cursor-pointer',
                isMinimized ? 'justify-center p-2.5' : 'justify-between px-2.5 py-1.5',
                activeLabel === label.id
                  ? 'bg-primary/10 text-primary font-semibold'
                  : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.99]',
                draggedLabelId === label.id ? 'opacity-40 scale-95' : '',
                dropTargetLabelId === label.id ? 'ring-2 ring-primary bg-primary/10 shadow-xs' : ''
              ]"
              :title="isMinimized ? label.name : 'Drag to reorder or drop onto another label to group'"
              @click="selectLabel(label.id)"
              @dragstart="handleDragStart(label, $event)"
              @dragover.prevent="handleDragOver(label, $event)"
              @dragleave="handleDragLeave(label)"
              @drop.prevent="handleDrop(label)"
              @dragend="handleDragEnd"
            >
              <div class="flex items-center gap-1.5 flex-1 min-w-0">
                <!-- Expand/Collapse Tree Caret for labels with sub-items -->
                <button
                  v-if="!isMinimized && hasChildren(label.id)"
                  type="button"
                  class="p-0.5 -ml-1 text-muted-foreground/70 hover:text-foreground rounded transition-transform"
                  @click.stop="toggleLabelExpand(label.id)"
                >
                  <ChevronRight
                    :class="[
                      'w-3 h-3 transition-transform duration-200',
                      isLabelExpanded(label.id) ? 'rotate-90' : ''
                    ]"
                  />
                </button>
                <div v-else-if="!isMinimized" class="w-2.5" />

                <!-- Label Color Dot -->
                <span :class="['w-2.5 h-2.5 rounded-full shrink-0', label.color]" />
                <span v-if="!isMinimized" class="truncate">{{ label.name }}</span>
              </div>

              <!-- Drag Grip Indicator on Hover -->
              <div v-if="!isMinimized" class="opacity-0 group-hover:opacity-60 transition-opacity">
                <GripVertical class="w-3 h-3 text-muted-foreground cursor-grab" />
              </div>
            </div>

            <!-- Nested Sub-labels (Tree Children) -->
            <div
              v-if="!isMinimized && hasChildren(label.id) && isLabelExpanded(label.id)"
              class="pl-4 ml-3 border-l border-border/50 space-y-0.5 animate-in slide-in-from-left-2 duration-150"
            >
              <div
                v-for="subLabel in getChildren(label.id)"
                :key="subLabel.id"
                :draggable="true"
                :class="[
                  'w-full flex items-center justify-between rounded-lg text-[11px] font-medium transition-all group relative px-2 py-1 cursor-pointer',
                  activeLabel === subLabel.id
                    ? 'bg-primary/10 text-primary font-semibold'
                    : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.99]',
                  draggedLabelId === subLabel.id ? 'opacity-40 scale-95' : '',
                  dropTargetLabelId === subLabel.id ? 'ring-2 ring-primary bg-primary/10' : ''
                ]"
                :title="'Sub-label of ' + label.name"
                @click="selectLabel(subLabel.id)"
                @dragstart="handleDragStart(subLabel, $event)"
                @dragover.prevent="handleDragOver(subLabel, $event)"
                @dragleave="handleDragLeave(subLabel)"
                @drop.prevent="handleDrop(subLabel)"
                @dragend="handleDragEnd"
              >
                <div class="flex items-center gap-2 flex-1 min-w-0">
                  <span :class="['w-2 h-2 rounded-full shrink-0', subLabel.color]" />
                  <span class="truncate">{{ subLabel.name }}</span>
                </div>
                <GripVertical class="w-2.5 h-2.5 opacity-0 group-hover:opacity-50 text-muted-foreground cursor-grab" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Footer: Storage Info (100% Dynamic from Backend Settings) -->
    <div class="w-full pt-2 border-t border-border/40 shrink-0">
      <div
        v-if="!isMinimized"
        class="p-2 rounded-xl bg-muted/30 border border-border/40 space-y-1.5"
        :title="storageStats ? `${storageStats.used_formatted} of ${storageStats.quota_formatted} (${storageStats.percentage}%)` : undefined"
      >
        <div class="flex items-center justify-between text-[11px] text-muted-foreground">
          <span class="flex items-center gap-1">
            <HardDrive class="w-3 h-3 text-primary" />
            {{ $t('system.mail.storage') }}
          </span>
          <span class="font-semibold text-foreground text-[10px]">
            {{ storageStats?.used_formatted || '0 B' }} / {{ storageStats?.quota_formatted || '...' }}
          </span>
        </div>
        <div class="w-full bg-border/60 h-1.5 rounded-full overflow-hidden">
          <div
            class="bg-primary h-full rounded-full transition-all duration-500"
            :style="{ width: `${Math.max(1, Math.min(100, storageStats?.percentage ?? 0))}%` }"
          />
        </div>
      </div>
      <div
        v-else
        class="flex justify-center p-1 text-muted-foreground cursor-help"
        :title="storageStats ? `${storageStats.used_formatted} / ${storageStats.quota_formatted} (${storageStats.percentage}%)` : undefined"
      >
        <HardDrive class="w-4 h-4 opacity-75 text-primary" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
  Inbox,
  Send,
  FileText,
  Trash2,
  AlertOctagon,
  HardDrive,
  Plus,
  ChevronDown,
  ChevronRight,
  GripVertical,
} from 'lucide-vue-next';
import type {
  useMailClient,
  MailLabel,
} from '@/modules/Core/System/composables/useMailClient';

type MailClient = ReturnType<typeof useMailClient>;

const props = defineProps<{
    isMinimized: boolean;
    activeFolder: MailClient['activeFolder']['value'];
    activeLabel: MailClient['activeLabel']['value'];
    folderCounts: MailClient['folderCounts']['value'];
    labels: MailClient['labels']['value'];
    storageStats?: MailClient['storageStats']['value'];
}>();

const emit = defineEmits<{
    (e: 'select-folder', folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'): void;
    (e: 'select-label', labelId: string): void;
    (e: 'update:labels', labels: MailLabel[]): void;
    (e: 'manage-labels'): void;
}>();

// Accordion Collapsible Sections State
const isFoldersOpen = ref(true);
const isLabelsOpen = ref(true);

// Tree Expansion State for Parent Labels
const expandedLabelIds = ref<Set<string>>(new Set());

const isLabelExpanded = (labelId: string) => {
    return expandedLabelIds.value.has(labelId);
};

const toggleLabelExpand = (labelId: string) => {
    if (expandedLabelIds.value.has(labelId)) {
        expandedLabelIds.value.delete(labelId);
    } else {
        expandedLabelIds.value.add(labelId);
    }
};

// Tree Structure Calculations
const rootLabels = computed(() => {
    return props.labels.filter(l => !l.parent_id || !props.labels.some(parent => parent.id === l.parent_id));
});

const getChildren = (parentId: string): MailLabel[] => {
    return props.labels.filter(l => l.parent_id === parentId);
};

const hasChildren = (labelId: string): boolean => {
    return props.labels.some(l => l.parent_id === labelId);
};

// Auto-expand parent if an active label is a child
watch(() => props.activeLabel, (newVal) => {
    if (newVal) {
        const found = props.labels.find(l => l.id === newVal);
        if (found?.parent_id) {
            expandedLabelIds.value.add(found.parent_id);
        }
    }
}, { immediate: true });

// Drag and Drop (DnD) State
const draggedLabelId = ref<string | null>(null);
const dropTargetLabelId = ref<string | null>(null);
const isHeaderDropHover = ref(false);

const handleDragStart = (label: MailLabel, event: DragEvent) => {
    draggedLabelId.value = label.id;
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', label.id);
    }
};

const handleDragOver = (label: MailLabel, event: DragEvent) => {
    if (draggedLabelId.value && draggedLabelId.value !== label.id) {
        dropTargetLabelId.value = label.id;
        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'move';
        }
    }
};

const handleDragLeave = (label: MailLabel) => {
    if (dropTargetLabelId.value === label.id) {
        dropTargetLabelId.value = null;
    }
};

const handleDrop = (targetLabel: MailLabel) => {
    if (!draggedLabelId.value || draggedLabelId.value === targetLabel.id) {
        handleDragEnd();
        return;
    }

    const currentLabels = [...props.labels];
    const draggedIdx = currentLabels.findIndex(l => l.id === draggedLabelId.value);
    const targetIdx = currentLabels.findIndex(l => l.id === targetLabel.id);

    if (draggedIdx === -1 || targetIdx === -1) {
        handleDragEnd();
        return;
    }

    const draggedItem: MailLabel = { ...currentLabels[draggedIdx] as MailLabel };

    // If dropped directly onto a parent, nest inside it; otherwise reorder
    if (!draggedItem.parent_id && !targetLabel.parent_id) {
        // Nest inside target label
        draggedItem.parent_id = targetLabel.id;
        expandedLabelIds.value.add(targetLabel.id);
    } else {
        // Inherit parent of target
        draggedItem.parent_id = targetLabel.parent_id || null;
    }

    currentLabels.splice(draggedIdx, 1);
    const newTargetIdx = currentLabels.findIndex(l => l.id === targetLabel.id);
    currentLabels.splice(newTargetIdx + 1, 0, draggedItem);

    emit('update:labels', currentLabels);
    handleDragEnd();
};

const handleHeaderDragOver = () => {
    if (draggedLabelId.value) {
        isHeaderDropHover.value = true;
    }
};

const handleHeaderDragLeave = () => {
    isHeaderDropHover.value = false;
};

const handleHeaderDrop = () => {
    if (!draggedLabelId.value) return;

    // Un-nest dragged label to top level
    const currentLabels = props.labels.map(l => {
        if (l.id === draggedLabelId.value) {
            return { ...l, parent_id: null };
        }
        return l;
    });

    emit('update:labels', currentLabels);
    isHeaderDropHover.value = false;
    handleDragEnd();
};

const handleDragEnd = () => {
    draggedLabelId.value = null;
    dropTargetLabelId.value = null;
    isHeaderDropHover.value = false;
};

const selectFolder = (folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam') => {
    emit('select-folder', folder);
};

const selectLabel = (labelId: string) => {
    emit('select-label', labelId);
};

const folderList: { id: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'; icon: any }[] = [
    { id: 'inbox', icon: Inbox },
    { id: 'sent', icon: Send },
    { id: 'drafts', icon: FileText },
    { id: 'trash', icon: Trash2 },
    { id: 'spam', icon: AlertOctagon },
];
</script>
