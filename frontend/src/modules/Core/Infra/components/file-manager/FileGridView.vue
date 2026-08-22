<template>
  <div class="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-4 p-4">
    <!-- Folders -->
    <div
      v-for="folder in paginatedFolders"
      :key="folder.path"
      class="group relative"
    >
      <ContextMenu>
        <ContextMenuTrigger as-child>
          <div
            class="group relative bg-background border border-border/40 rounded-xl overflow-hidden cursor-pointer transition-colors duration-200 hover:border-primary/40 shadow-none hover:bg-accent/5"
            :class="{ 
              'bg-primary/5 border-primary/40 shadow-none': isSelected(folder.path),
              'ring-2 ring-primary/20 border-primary/50 bg-primary/5': dropTarget === folder.path
            }"
            draggable="true"
            @click="selectItem(folder); navigateToPath(folder.path)"
            @contextmenu="selectItem(folder)"
            @dragstart="(e) => onDragStart(e, folder, 'folder')"
            @dragend="onDragEnd"
            @dragover.prevent="(e) => onDragOver(e, folder)"
            @dragleave="onDragLeave"
            @drop="(e) => onDrop(e, folder)"
          >
            <div
              class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-[opacity,transform] scale-90 group-hover:scale-100"
              :class="{ 'opacity-100 scale-100': isSelected(folder.path) }"
            >
              <Checkbox
                :checked="isSelected(folder.path)"
                class="bg-background/80 backdrop-blur-sm border-primary/30"
                :aria-label="$t('common.actions.selectRow') + ': ' + folder.name"
                @update:checked="() => toggleSelection(folder.path)"
                @click.stop
              />
            </div>
            <div class="aspect-square flex flex-col items-center justify-center p-4 transition-colors">
              <Folder
                class="w-12 h-12 text-muted-foreground/40 transition-transform duration-300 group-hover:scale-105 group-active:scale-95"
                stroke-width="1.5"
              />
            </div>
            <div
              class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-[opacity,transform] scale-90 group-hover:scale-100"
              @click.stop
            >
              <FileActionDropdown :item="folder">
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 rounded-lg bg-background/80 backdrop-blur-sm border border-border/40 shadow-sm hover:bg-accent hover:text-accent-foreground"
                  :aria-label="$t('infra.fileManager.actions.quickActions')"
                >
                  <MoreVertical class="w-4 h-4" />
                </Button>
              </FileActionDropdown>
            </div>
            <div class="p-3 border-t border-border/40 bg-transparent">
              <p
                class="text-xs font-semibold truncate text-center text-foreground/90 px-1"
                :title="folder.name"
              >
                {{ folder.name }}
              </p>
              <p class="text-xs text-muted-foreground text-center mt-1 font-medium">
                Folder
              </p>
            </div>
          </div>
        </ContextMenuTrigger>
        <FileContextMenu :item="folder" />
      </ContextMenu>
    </div>

    <!-- Files -->
    <div
      v-for="file in paginatedFiles"
      :key="file.path"
      class="group relative"
    >
      <ContextMenu>
        <ContextMenuTrigger as-child>
          <div
            class="group relative bg-background border border-border/40 rounded-xl hover:border-primary/40 transition-colors duration-200 cursor-pointer overflow-hidden shadow-none hover:bg-accent/5"
            :class="{ 'bg-primary/5 border-primary/40 shadow-none': isSelected(file.path) }"
            draggable="true"
            @click="selectItem(file); $emit('preview', file)"
            @contextmenu="selectItem(file)"
            @dragstart="(e) => onDragStart(e, file, 'file')"
            @dragend="onDragEnd"
          >
            <div
              class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-[opacity,transform] scale-90 group-hover:scale-100"
              :class="{ 'opacity-100 scale-100': isSelected(file.path) }"
            >
              <Checkbox
                :checked="isSelected(file.path)"
                class="bg-background/80 backdrop-blur-sm border-primary/30"
                :aria-label="$t('common.actions.selectRow') + ': ' + file.name"
                @update:checked="toggleSelection(file.path)"
                @click.stop
              />
            </div>
            <div class="aspect-square flex items-center justify-center overflow-hidden relative">
              <img 
                v-if="isImage(file)" 
                :src="file.url" 
                :alt="file.name"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                loading="lazy"
              >
              <div
                v-else-if="isVideo(file)"
                class="relative w-full h-full flex items-center justify-center"
              >
                <Video
                  class="w-12 h-12 text-muted-foreground/50 transition-transform group-hover:scale-110"
                  stroke-width="1.5"
                />
                <div class="absolute inset-0 bg-black/5 flex items-center justify-center">
                  <PlayCircle class="w-8 h-8 text-white/50 opacity-0 group-hover:opacity-100 transition-opacity" />
                </div>
                <div class="absolute bottom-2 right-2 bg-black/70 text-white text-[9px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider backdrop-blur-sm">
                  {{ file.extension?.toUpperCase() }}
                </div>
              </div>
              <div
                v-else
                class="flex flex-col items-center"
              >
                <FileText
                  class="w-12 h-12 text-muted-foreground/30 transition-transform duration-300 group-hover:scale-110"
                  stroke-width="1.5"
                />
                <span class="mt-2 text-xs font-semibold text-muted-foreground">{{ file.extension }}</span>
              </div>
              <div
                class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-[opacity,transform] scale-90 group-hover:scale-100"
                @click.stop
              >
                <FileActionDropdown
                  :item="file"
                  @preview="$emit('preview', file)"
                >
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 rounded-lg bg-background/80 backdrop-blur-sm border border-border/40 shadow-sm hover:bg-accent hover:text-accent-foreground"
                  >
                    <MoreVertical class="w-4 h-4" />
                  </Button>
                </FileActionDropdown>
              </div>
            </div>
            <div class="p-3 border-t border-border/40 bg-transparent">
              <p
                class="text-xs font-semibold truncate text-foreground/90 px-1"
                :title="file.name"
              >
                {{ file.name }}
              </p>
              <p class="text-[10px] text-muted-foreground mt-1 flex justify-between items-center px-1 font-medium italic">
                <span>{{ formatFileSize(file.size) }}</span>
                <span class="uppercase text-[9px] not-italic font-bold tracking-tighter opacity-60">{{ file.extension }}</span>
              </p>
            </div>
          </div>
        </ContextMenuTrigger>
        <FileContextMenu
          :item="file"
          @preview="$emit('preview', file)"
        />
      </ContextMenu>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { inject } from 'vue';
import {
  FileText,
  Folder,
  MoreVertical,
  PlayCircle,
  Video,
} from 'lucide-vue-next';
import { 
    Checkbox, 
    ContextMenu, 
    ContextMenuTrigger, 
    Button
} from '@/shared/components/ui';
import FileContextMenu from './FileContextMenu.vue';
import FileActionDropdown from './FileActionDropdown.vue';
import type { FileItem, FolderItem } from '@/modules/Core/Infra/types/file-manager';
import { FileManagerKey } from '@/engine/keys';

const {
    paginatedFolders,
    paginatedFiles,
    selectedItems,
    dropTarget,
    navigateToPath,
    toggleSelection,
    moveItem,
    formatFileSize,
    isImage,
    isVideo,
    selectItem
} = inject(FileManagerKey)!;

defineEmits<{
    (e: 'preview', item: FileItem): void
}>();

const isSelected = (path: string) => selectedItems.value.some((item: FileItem | FolderItem) => item.path === path);

const onDragStart = (event: DragEvent, item: FileItem | FolderItem, type: 'file' | 'folder') => {
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', JSON.stringify({ path: item.path, type }));
    }
};

const onDragEnd = () => {
    // Handled by global state
};

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
    } catch (e: unknown) {
        logger.error('Drop failed', e);
    } finally {
        dropTarget.value = null;
    }
};
</script>
