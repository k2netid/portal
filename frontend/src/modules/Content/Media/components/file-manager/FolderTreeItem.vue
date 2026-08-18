<template>
  <div 
    :style="{ marginLeft: `${depth * 0.5}rem` }"
    @dragover.prevent="(e) => onDragOver(e, folder)"
    @dragleave="onDragLeave"
    @drop="(e) => onDrop(e, folder)"
  >
    <ContextMenu>
      <ContextMenuTrigger as-child>
        <button
          :class="[
            'w-full flex items-center gap-2 text-[13px] h-9 px-2 rounded-lg transition-colors duration-200 group/item',
            isActive ? 'bg-primary/10 text-primary font-bold shadow-none' : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.98]',
            dropTarget === folder.path ? 'bg-primary/20 ring-1 ring-primary' : ''
          ]"
          :title="folder.name"
          @click="navigateToPath(folder.path)"
        >
          <div 
            v-if="hasChildren"
            class="w-4 h-4 flex items-center justify-center rounded hover:bg-black/10 transition-colors"
            @click.stop="toggleExpanded(folder.path)"
          >
            <ChevronDown 
              v-if="isExpanded" 
              class="w-3.5 h-3.5 transition-transform duration-200"
            />
            <ChevronRight 
              v-else 
              class="w-3.5 h-3.5 transition-transform duration-200" 
            />
          </div>
          <span
            v-else
            class="w-4"
          />
                    
          <FolderOpen
            v-if="isActive || isParentOfActive"
            class="w-4 h-4 shrink-0 transition-transform group-hover/item:scale-110"
            :class="isActive ? 'text-primary-foreground' : 'text-primary'"
          />
          <Folder
            v-else
            class="w-4 h-4 shrink-0 opacity-70 group-hover/item:opacity-100 transition-[opacity,transform] group-hover/item:scale-110"
          />
          <span class="truncate font-medium">{{ folder.name }}</span>
        </button>
      </ContextMenuTrigger>
      <FileContextMenu :item="folder" />
    </ContextMenu>

    <div v-if="isExpanded && hasChildren">
      <FolderTreeItem 
        v-for="child in folder.children" 
        :key="child.path" 
        :folder="child"
        :depth="depth + 1"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { inject, computed } from 'vue';
import {
  ChevronDown,
  ChevronRight,
  Folder,
  FolderOpen,
} from 'lucide-vue-next';
import { 
    ContextMenu,
    ContextMenuTrigger
} from '@/shared/components/ui';
import FileContextMenu from './FileContextMenu.vue';
import { FileManagerKey } from '@/engine/keys';
import type { FolderItem } from '@/modules/Content/Media/types/file-manager';


const props = defineProps<{
    folder: FolderItem;
    depth: number;
}>();

const {
    currentPath,
    dropTarget,
    navigateToPath,
    toggleFolderExpanded: toggleExpanded,
    isFolderExpanded,
    moveItem
} = inject(FileManagerKey)!;



const hasChildren = computed(() => (props.folder.children?.length ?? 0) > 0);
const isExpanded = computed(() => isFolderExpanded(props.folder.path));
const isActive = computed(() => currentPath.value === props.folder.path);
const isParentOfActive = computed(() => currentPath.value.startsWith(props.folder.path + '/'));
const onDragOver = (event: DragEvent, folder: FolderItem) => {
    event.preventDefault();
    dropTarget.value = folder.path;
};

const onDragLeave = () => {
    dropTarget.value = null;
};

const onDrop = async (event: DragEvent, targetFolder: FolderItem) => {
    event.preventDefault();
    const data = event.dataTransfer?.getData('text/plain');
    if (!data) return;
    
    try {
        const { path: sourcePath, type } = JSON.parse(data);
        if (sourcePath === targetFolder.path) return;
        await moveItem(sourcePath, targetFolder.path, type);
    } catch (e) {
        logger.error('Drop failed', e);
    } finally {
        dropTarget.value = null;
    }
};
</script>
