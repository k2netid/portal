<template>
  <ContextMenu>
    <ContextMenuTrigger class="flex-1 h-full w-full block">
      <div class="overflow-x-auto h-full">
        <table class="min-w-full divide-y divide-border/40">
          <thead class="bg-muted/30">
            <tr>
              <th class="px-6 py-3 text-left w-12">
                <Checkbox
                    :aria-label="$t('media.file_manager.bulk.selectAll')"
                  :checked="allSelected"
                  @update:checked="toggleSelectAll()"
                />
              </th>

              <th class="px-6 py-3 text-left w-16">
                {{ $t('media.table.media') }}
              </th>
              <th 
                class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider relative group/header whitespace-nowrap"
                :style="{ width: nameColumnWidth + 'px', minWidth: nameColumnWidth + 'px' }"
              >
                <div class="flex items-center gap-2">
                  {{ $t('media.table.name') }}
                </div>
                <!-- Resize Handle -->
                <div 
                  class="absolute right-0 top-0 h-full w-1 cursor-col-resize hover:bg-primary/30 active:bg-primary group-hover/header:bg-border/50 transition-colors z-10"
                  @mousedown="startResizing"
                />
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                {{ $t('media.table.type') }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                {{ $t('media.table.size') }}
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground tracking-wider">
                {{ $t('media.table.folder') }}
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground tracking-wider">
                {{ $t('media.table.actions') }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-card divide-y divide-border/40">
            <!-- Folders -->
            <ContextMenu 
              v-for="folder in currentFolders" 
              :key="'folder-list-' + folder.id"
            >
              <ContextMenuTrigger as-child>
                <tr 
                  class="hover:bg-muted/30 cursor-pointer group"
                  :class="{ 'bg-primary/5': selectedFolders.includes(folder.id) }"
                  @click="isTrashMode ? toggleFolderSelection(folder) : selectFolder(folder.id)"
                >
                  <td
                    class="px-6 py-4 whitespace-nowrap"
                    @click.stop
                  >
                    <Checkbox
                        :aria-label="$t('media.select')"
                      v-if="isTrashMode"
                      :checked="selectedFolders.includes(folder.id)"
                      @update:checked="toggleFolderSelection(folder)"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Folder
                      class="w-5 h-5 text-muted-foreground/60"
                      stroke-width="1.5"
                    />
                  </td>
                  <td
                    class="px-6 py-4"
                    :style="{ width: nameColumnWidth + 'px', maxWidth: nameColumnWidth + 'px' }"
                  >
                    <div class="flex items-center gap-2 overflow-hidden">
                      <div
                        class="text-sm font-medium text-foreground truncate"
                        :title="folder.name"
                      >
                        {{ folder.name }}
                      </div>
                      <Badge
                        v-if="folder.is_shared"
                        variant="secondary"
                        class="text-[10px] h-4 px-1 bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800"
                      >
                        {{ $t('media.shared') }}
                      </Badge>
                    </div>
                    <div class="text-xs text-muted-foreground">
                      {{ $t('media.folder') }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge
                      variant="outline"
                      class="font-normal border-blue-200 text-blue-600 bg-blue-50/50 dark:bg-blue-900/10 dark:text-blue-300 dark:border-blue-800"
                    >
                      FOLDER
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                    -
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                    {{ folder.parent?.name || '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 text-destructive rounded-lg"
                        :aria-label="$t('media.actions.delete')"
                        @click.stop="deleteFolder(folder)"
                      >
                        <Trash2
                          class="w-4 h-4"
                          stroke-width="1.5"
                        />
                      </Button>
                    </div>
                  </td>
                </tr>
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

            <!-- Media -->
            <ContextMenu 
              v-for="media in mediaList" 
              :key="media.id"
            >
              <ContextMenuTrigger as-child>
                <tr 
                  class="hover:bg-muted/30 cursor-pointer group"
                  :class="{ 'bg-primary/5': selectedMedia.includes(media.id) }"
                  @click="viewMedia(media)"
                >
                  <td
                    class="px-6 py-4 whitespace-nowrap"
                    @click.stop
                  >
                    <Checkbox
                        :aria-label="$t('media.select')"
                      :checked="selectedMedia.includes(media.id)"
                      @update:checked="toggleMediaSelection(media)"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div
                      class="w-10 h-10 bg-muted/20 rounded-xl flex items-center justify-center p-0.5"
                      :data-media-id="media.id"
                    >
                      <LazyImage
                        v-if="media.mime_type?.startsWith('image/')"
                        :src="media.thumbnail_url || media.url"
                        :alt="media.alt || media.name"
                        image-class="w-full h-full object-cover rounded-lg shadow-none"
                        @error="handleImageError($event, media)"
                      />
                      <VideoIcon
                        v-else-if="media.mime_type?.startsWith('video/')"
                        class="w-5 h-5 text-muted-foreground/40"
                        stroke-width="1.5"
                      />
                      <FileIcon
                        v-else
                        class="w-5 h-5 text-muted-foreground/40"
                        stroke-width="1.5"
                      />
                    </div>
                  </td>
                  <td
                    class="px-6 py-4"
                    :style="{ width: nameColumnWidth + 'px', maxWidth: nameColumnWidth + 'px' }"
                  >
                    <div class="flex items-center gap-2 overflow-hidden">
                      <div
                        class="text-sm font-medium text-foreground truncate"
                        :title="media.name"
                      >
                        {{ media.name }}
                      </div>
                      <Badge
                        v-if="media.is_shared"
                        variant="secondary"
                        class="text-[10px] h-4 px-1 bg-blue-50 text-blue-600 border-blue-100"
                      >
                        {{ $t('media.shared') }}
                      </Badge>
                    </div>
                    <div
                      v-if="media.alt"
                      class="text-sm text-muted-foreground truncate"
                      :title="media.alt"
                    >
                      {{ media.alt }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge
                      variant="secondary"
                      class="font-normal"
                    >
                      {{ media.mime_type.split('/')[1]?.toUpperCase() || media.mime_type }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                    {{ formatFileSize(media.size) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
                    {{ media.folder?.name || '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <template v-if="isTrashMode">
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-primary rounded-lg"
                          :aria-label="$t('media.actions.restore')"
                          @click.stop="restoreMedia(media)"
                        >
                          <RefreshCw
                            class="w-4 h-4"
                            stroke-width="1.5"
                          />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-destructive rounded-lg"
                          :aria-label="$t('media.actions.deletePermanent')"
                          @click.stop="deleteMedia(media)"
                        >
                          <Trash2
                            class="w-4 h-4"
                            stroke-width="1.5"
                          />
                        </Button>
                      </template>
                      <template v-else>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 rounded-lg"
                          :aria-label="$t('media.actions.view')"
                          @click.stop="viewMedia(media)"
                        >
                          <Eye
                            class="w-4 h-4"
                            stroke-width="1.5"
                          />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 rounded-lg"
                          :aria-label="$t('media.actions.edit')"
                          @click.stop="editMedia(media)"
                        >
                          <Edit
                            class="w-4 h-4"
                            stroke-width="1.5"
                          />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-destructive rounded-lg"
                          :aria-label="$t('media.actions.delete')"
                          @click.stop="deleteMedia(media)"
                        >
                          <Trash2
                            class="w-4 h-4"
                            stroke-width="1.5"
                          />
                        </Button>
                      </template>
                    </div>
                  </td>
                </tr>
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
          </tbody>
        </table>
      </div>
    </ContextMenuTrigger>
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
</template>

<script setup lang="ts">
import { ref, inject, computed } from 'vue';

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
  VideoIcon,
} from 'lucide-vue-next';
import { 
    Badge, 
    Button, 
    Checkbox, 
    LazyImage,
    ContextMenu,
    ContextMenuTrigger,
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuSeparator
} from '@/shared/components/ui';
import { MediaManagerKey } from '@/engine/keys';
import type { Media } from '@/modules/Content/Media/types/media';

const {
    currentFolders,
    mediaList,
    selectedMedia,
    selectedFolders,
    isTrashMode,
    toggleMediaSelection,
    toggleFolderSelection,
    toggleSelectAll,
    selectFolder,
    deleteFolder,
    restoreFolder,
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

const allSelected = computed(() => {
    return mediaList.value.length > 0 && selectedMedia.value.length === mediaList.value.length;
});

const nameColumnWidth = ref(300);
const isResizing = ref(false);
const startX = ref(0);
const startWidth = ref(0);

const startResizing = (e: MouseEvent) => {
    isResizing.value = true;
    startX.value = e.pageX;
    startWidth.value = nameColumnWidth.value;

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', stopResizing);
    document.body.style.userSelect = 'none';
};

const handleMouseMove = (e: MouseEvent) => {
    if (!isResizing.value) return;
    const diff = e.pageX - startX.value;
    const newWidth = Math.max(150, Math.min(800, startWidth.value + diff));
    nameColumnWidth.value = newWidth;
};

const stopResizing = () => {
    isResizing.value = false;
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', stopResizing);
    document.body.style.userSelect = '';
};

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
