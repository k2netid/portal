<template>
  <TooltipProvider>
    <ContextMenu>
      <ContextMenuTrigger class="flex-1 h-full">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-4 p-4 min-h-[200px] content-start">
          <!-- Folders -->
          <ContextMenu
            v-for="folder in currentFolders"
            :key="'folder-' + folder.id"
          >
            <ContextMenuTrigger>
              <div
                :class="[
                  'group relative bg-background border border-border/40 rounded-xl overflow-hidden cursor-pointer transition-[border-color,background-color] duration-200 hover:border-primary/50 shadow-none hover:bg-accent/5 h-full',
                  selectedFolders.includes(folder.id) ? 'ring-2 ring-indigo-500 border-indigo-500' : ''
                ]"
                @click="isTrashMode ? toggleFolderSelection(folder) : selectFolder(folder.id)"
              >
                <!-- Folder Checkbox -->
                <div
                  v-if="isTrashMode"
                  class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity"
                  :class="{ 'opacity-100': selectedFolders.includes(folder.id) }"
                >
                  <Checkbox
                      :aria-label="$t('media.select')"
                    :checked="selectedFolders.includes(folder.id)"
                    @update:checked="toggleFolderSelection(folder)"
                    @click.stop
                  />
                </div>
                <div class="aspect-square bg-blue-50/20 dark:bg-blue-900/5 flex flex-col items-center justify-center p-2">
                  <div class="relative">
                    <Folder
                      class="w-10 h-10 text-blue-400 fill-blue-400/5"
                      stroke-width="1.5"
                    />
                    <div
                      v-if="(folder.children_count || 0) > 0"
                      class="absolute -top-1 -right-1 bg-blue-500 text-white text-[10px] w-4 h-4 flex items-center justify-center rounded-full border border-white"
                    >
                      {{ folder.children_count }}
                    </div>
                  </div>
                                    
                  <!-- Shared Folder Indicator -->
                  <div
                    v-if="folder.is_shared"
                    class="absolute top-2 right-2"
                  >
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <div class="bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 p-1 rounded-md border border-blue-100 dark:border-blue-800">
                          <Users class="w-3 h-3" />
                        </div>
                      </TooltipTrigger>
                      <TooltipContent>
                        <p>{{ $t('media.shared') }}</p>
                      </TooltipContent>
                    </Tooltip>
                  </div>

                  <p
                    class="mt-2 text-[11px] font-medium text-foreground truncate w-full text-center px-1"
                    :title="folder.name"
                  >
                    {{ folder.name }}
                  </p>
                </div>
                <!-- Folder Actions Overlay -->
                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-destructive hover:bg-destructive/10 rounded-lg"
                    :aria-label="$t('media.actions.delete')"
                    @click.stop="deleteFolder(folder)"
                  >
                    <Trash2
                      class="w-4 h-4"
                      stroke-width="1.5"
                    />
                  </Button>
                </div>
              </div>
            </ContextMenuTrigger>
            <ContextMenuContent>
              <template v-if="isTrashMode">
                <ContextMenuItem @click="restoreFolder(folder)">
                  <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                  {{ $t('media.actions.restore') }}
                </ContextMenuItem>
                <ContextMenuSeparator />
                <ContextMenuItem
                  class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                  @click="deleteFolder(folder)"
                >
                  <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                  {{ $t('media.actions.deletePermanent') }}
                </ContextMenuItem>
              </template>
              <template v-else>
                <ContextMenuItem @click="selectFolder(folder.id)">
                  <FolderOpen class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                  {{ $t('media.actions.open') }}
                </ContextMenuItem>
                <ContextMenuSeparator />
                <ContextMenuItem
                  class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                  @click="deleteFolder(folder)"
                >
                  <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                  {{ $t('media.actions.delete') }}
                </ContextMenuItem>
              </template>
            </ContextMenuContent>
          </ContextMenu>

          <!-- Media Items -->
          <ContextMenu
            v-for="media in mediaList"
            :key="media.id"
          >
            <ContextMenuTrigger>
              <div
                class="group relative bg-background border border-border/40 rounded-xl overflow-hidden cursor-pointer transition-[border-color,background-color] duration-200 hover:border-primary/50 shadow-none hover:bg-accent/5 h-full"
                :class="selectedMedia.includes(media.id) ? 'ring-2 ring-indigo-500 border-indigo-500' : ''"
                @click="toggleMediaSelection(media)"
              >
                <!-- Checkbox -->
                <div
                  class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity"
                  :class="{ 'opacity-100': selectedMedia.includes(media.id) }"
                >
                  <Checkbox
                      :aria-label="$t('media.select')"
                    :checked="selectedMedia.includes(media.id)"
                    @update:checked="toggleMediaSelection(media)"
                    @click.stop
                  />
                </div>
                <div
                  class="aspect-square bg-muted/30 flex items-center justify-center relative group-content"
                  :data-media-id="media.id"
                >
                  <LazyImage
                    v-if="media.mime_type?.startsWith('image/')"
                    :src="media.thumbnail_url || media.url"
                    :alt="media.alt || media.name"
                    image-class="w-full h-full object-cover"
                    @error="handleImageError($event, media)"
                  />
                  <div
                    v-else-if="media.mime_type?.startsWith('video/')"
                    class="relative w-full h-full flex items-center justify-center bg-muted/50"
                  >
                    <VideoIcon
                      class="w-12 h-12 text-muted-foreground"
                      stroke-width="1.5"
                    />
                    <div class="absolute bottom-1 right-1 bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded">
                      {{ media.mime_type.split('/')[1]?.toUpperCase() }}
                    </div>
                  </div>
                  <div
                    v-else
                    class="text-muted-foreground"
                  >
                    <FileIcon
                      class="w-12 h-12"
                      stroke-width="1.5"
                    />
                  </div>
                                    
                  <!-- Shared Indicator (Absolute) -->
                  <div
                    v-if="media.is_shared"
                    class="absolute top-2 right-2 z-10"
                  >
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <div class="bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 p-1 rounded-md border border-blue-100 dark:border-blue-800 shadow-sm">
                          <Users class="w-3 h-3" />
                        </div>
                      </TooltipTrigger>
                      <TooltipContent>
                        <p>{{ $t('media.shared') }}</p>
                      </TooltipContent>
                    </Tooltip>
                  </div>
                </div>
                <div class="p-1.5 border-t border-border bg-card">
                  <p
                    class="text-[11px] font-medium text-foreground truncate mb-1"
                    :title="media.name"
                  >
                    {{ media.name }}
                  </p>
                  <div class="flex flex-col gap-0.5">
                    <p class="text-[10px] text-muted-foreground leading-none">
                      {{ formatFileSize(media.size) }}
                    </p>
                  </div>
                </div>
              </div>
            </ContextMenuTrigger>
            <ContextMenuContent>
              <template v-if="isTrashMode">
                <ContextMenuItem @click="restoreMedia(media)">
                  <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                  {{ $t('media.actions.restore') }}
                </ContextMenuItem>
                <ContextMenuSeparator />
                <ContextMenuItem
                  class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                  @click="deleteMedia(media)"
                >
                  <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                  {{ $t('media.actions.deletePermanent') }}
                </ContextMenuItem>
              </template>
              <template v-else>
                <ContextMenuItem @click="viewMedia(media)">
                  <Eye class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                  {{ $t('media.actions.view') }}
                </ContextMenuItem>
                <ContextMenuItem @click="editMedia(media)">
                  <Edit class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                  {{ $t('media.actions.edit') }}
                </ContextMenuItem>
                <ContextMenuItem @click="downloadMedia(media)">
                  <Download class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                  {{ $t('media.actions.download') }}
                </ContextMenuItem>
                <ContextMenuSeparator />
                <ContextMenuItem @click="copyMediaUrl(media)">
                  <Link class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                  {{ $t('media.actions.copyUrl') }}
                </ContextMenuItem>
                <ContextMenuSeparator />
                <ContextMenuItem
                  class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                  @click="deleteMedia(media)"
                >
                  <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                  {{ $t('media.actions.delete') }}
                </ContextMenuItem>
              </template>
            </ContextMenuContent>
          </ContextMenu>
        </div>
      </ContextMenuTrigger>
            
      <!-- Global Context Menu Content -->
      <ContextMenuContent>
        <template v-if="!isTrashMode">
          <ContextMenuItem @click="showFolderModal = true">
            <FolderPlus class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
            {{ $t('media.newFolder') }}
          </ContextMenuItem>
          <ContextMenuItem @click="showUploadModal = true">
            <Plus class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
            {{ $t('media.upload') }}
          </ContextMenuItem>
          <ContextMenuSeparator />
          <ContextMenuItem @click="fetchMedia(); fetchFolders();">
            <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
            {{ $t('media.actions.refresh') }}
          </ContextMenuItem>
        </template>
        <template v-else>
          <ContextMenuItem @click="emptyTrash">
            <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
            {{ $t('media.emptyTrash') }}
          </ContextMenuItem>
        </template>
      </ContextMenuContent>
    </ContextMenu>
  </TooltipProvider>
</template>

<script setup lang="ts">
import { inject } from 'vue';
import {
  Download,
  Edit,
  Eye,
  FileIcon,
  Folder,
  FolderOpen,
  FolderPlus,
  Link,
  Plus,
  RefreshCw,
  Trash2,
  Users,
  VideoIcon,
} from 'lucide-vue-next';
import { 
    Button, 
    Checkbox, 
    LazyImage,
    ContextMenu,
    ContextMenuTrigger,
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuSeparator,
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger
} from '@/shared/components/ui';
import { MediaManagerKey } from '@/engine/keys';
import type { Media } from '@/modules/Media/types/media';

const {
    currentFolders,
    mediaList,
    selectedMedia,
    selectedFolders,
    isTrashMode,
    selectFolder,
    deleteFolder,
    restoreFolder,
    toggleMediaSelection,
    toggleFolderSelection,
    restoreMedia,
    deleteMedia,
    viewMedia,
    editMedia,
    downloadMedia,
    copyMediaUrl,
    // Modals
    showFolderModal,
    showUploadModal,
    fetchMedia,
    fetchFolders,
    emptyTrash
} = inject(MediaManagerKey)!;

// defineEmits is unused as we use injected functions directly
// const emit = defineEmits(['select-folder']);
// Actually, I should remove all emits and use the injected functions directly in template.
// But wait, the template uses $emit. I need to update the template too.

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const handleImageError = (event: Event, media: Media) => {
    const img = event.target as HTMLImageElement;
    const currentSrc = img.src || img.getAttribute('src');
    if (currentSrc && currentSrc.includes('_thumb.')) {
        if (media && media.url && media.url !== currentSrc) {
            img.src = media.url;
            return;
        }
        const originalSrc = currentSrc.replace('_thumb.', '.').replace('/thumbnails/', '/');
        if (originalSrc !== currentSrc) {
            img.src = originalSrc;
            return;
        }
    }
    if (img.dataset?.originalUrl && img.src !== img.dataset.originalUrl) {
        img.src = img.dataset.originalUrl;
    }
};
</script>
