<template>
  <div 
    :class="[
      'h-full flex flex-col overflow-hidden bg-white lg:bg-transparent border-r border-border/40 lg:border-r-0',
      sidebarCollapsed ? 'w-0' : 'w-72',
      isMounted ? 'transition-[width] duration-300 ease-in-out' : ''
    ]"
  >
    <!-- Sidebar Header (Title) -->
    <div
      v-if="!sidebarCollapsed"
      class="h-14 flex items-center px-2 shrink-0"
    >
      <h2 class="text-sm font-bold text-foreground whitespace-nowrap px-4">
        {{ $t('infra.fileManager.title') }}
      </h2>
    </div>
    <div
      v-if="!sidebarCollapsed"
      class="px-4 shrink-0"
    >
      <div class="h-px bg-border/40 w-full" />
    </div>

    <div
      v-if="!sidebarCollapsed"
      class="flex items-center justify-between p-4 pb-0"
    >
      <h2 class="text-xs font-semibold text-foreground/70 flex items-center">
        <Folder class="w-3 h-3 mr-2" />
        {{ $t('infra.fileManager.labels.folders') }}
      </h2>
    </div>
        
    <div
      v-if="!sidebarCollapsed"
      class="flex-1 overflow-y-auto custom-scrollbar p-2 pr-4 space-y-0.5 mt-2"
    >
      <!-- Root folder -->
      <div 
        @dragover.prevent="(e) => onDragOver(e, { path: '/', name: 'Root', updated_at: '' } as FolderItem)"
        @dragleave="onDragLeave"
        @drop="(e) => onDrop(e, { path: '/', name: 'Root', updated_at: '' } as FolderItem)"
      >
        <ContextMenu>
          <ContextMenuTrigger as-child>
            <button
              :class="[
                'w-full flex items-center gap-2 text-[13px] h-9 px-2 rounded-lg transition-colors duration-200 group/root',
                !showTrashView && currentPath === '/' ? 'bg-primary/10 text-primary font-bold shadow-none' : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.98]',
                dropTarget === '/' ? 'bg-primary/20 ring-1 ring-primary' : ''
              ]"
              @click="navigateToPath('/')"
            >
              <ChevronDown 
                v-if="folderTree.length > 0" 
                class="w-3.5 h-3.5 shrink-0" 
              />
              <span
                v-else
                class="w-3.5"
              />
              <FolderOpen
                v-if="currentPath === '/'"
                class="w-4 h-4 shrink-0 transition-transform group-hover/root:scale-110 text-primary"
              />
              <Folder
                v-else
                class="w-4 h-4 shrink-0 opacity-70 group-hover/root:opacity-100 transition-shadow group-hover/root:scale-110"
              />
              <span class="truncate font-medium">{{ $t('infra.fileManager.nav.root') }}</span>
            </button>
          </ContextMenuTrigger>
          <FileContextMenu :item="{ path: '/', name: 'Root', updated_at: '' }" />
        </ContextMenu>
      </div>
            
      <!-- Recursive Tree -->
      <FolderTreeItem 
        v-for="folder in folderTree" 
        :key="folder.path" 
        :folder="folder"
        :depth="1"
      />
    </div>

    <!-- Trash navigation (Bottom) -->
    <div
      v-if="!sidebarCollapsed"
      class="px-4 shrink-0"
    >
      <div class="h-px bg-border/40 w-full" />
    </div>
    <div
      v-if="!sidebarCollapsed"
      class="p-3 bg-transparent mt-auto"
    >
      <button
        :class="[
          'w-full flex items-center gap-3 text-sm h-10 px-3 rounded-lg transition-colors duration-200 group/trash relative',
          showTrashView 
            ? 'bg-destructive/10 text-destructive' 
            : 'text-muted-foreground hover:bg-destructive/5 hover:text-destructive active:scale-[0.98]'
        ]"
        @click="showTrashView = !showTrashView"
      >
        <Trash2 
          :class="[
            'w-4 h-4 shrink-0 transition-transform duration-300',
            showTrashView ? 'scale-110' : 'group-hover:rotate-12'
          ]" 
        />
        <span class="truncate font-semibold tracking-tight">{{ $t('infra.fileManager.trash.title') }}</span>
                
        <div 
          v-if="trashCount && trashCount > 0" 
          :class="[
            'ml-auto px-1.5 py-0.5 rounded text-[10px] font-bold transition-all',
            showTrashView 
              ? 'bg-destructive text-white' 
              : 'bg-destructive/10 text-destructive group-hover:bg-destructive group-hover:text-white'
          ]"
        >
          {{ trashCount }}
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { inject } from 'vue';
import {
  ChevronDown,
  Folder,
  FolderOpen,
  Trash2,
} from 'lucide-vue-next';
import { 
    ContextMenu,
    ContextMenuTrigger
} from '@/shared/components/ui';
import FileContextMenu from './FileContextMenu.vue';
import type { FolderItem } from '@/modules/Core/Infra/types/file-manager';
import FolderTreeItem from './FolderTreeItem.vue';
import { FileManagerKey } from '@/engine/keys';

const {
    currentPath,
    sidebarCollapsed,
    folderTree,
    dropTarget,
    navigateToPath,
    moveItem,
    showTrashView,
    trashCount,
    isMounted
} = inject(FileManagerKey)!;

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
