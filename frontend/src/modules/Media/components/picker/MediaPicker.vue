<template>
  <div class="media-picker">
    <slot
      name="trigger"
      :open="openModal"
    >
      <Button
        type="button"
        variant="outline"
        class="w-full h-12 border-2 border-dashed flex items-center justify-center gap-2"
        @click="openModal"
      >
        <ImageIcon class="w-4 h-4" />
        {{ label || $t('media.modals.picker.select') }}
      </Button>
    </slot>


    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 z-[100000] pointer-events-none"
      >
        <!-- Removed centering wrapper to allow free movement -->
        <div 
          ref="modalRef"
          class="bg-card border border-border/40 shadow-none rounded-xl max-w-4xl w-full max-h-[90vh] flex flex-col pointer-events-auto absolute top-1/2 left-1/2"
          :style="{ transform: `translate(calc(-50% + ${position.x}px), calc(-50% + ${position.y}px))` }"
        >
          <div 
            class="flex items-center justify-between p-4 border-b border-border/40 cursor-move select-none active:cursor-grabbing"
            @mousedown="startDrag"
          >
            <h3 class="text-lg font-semibold">
              {{ $t('media.modals.picker.title') }}
            </h3>
            <Button
              type="button"
              variant="ghost"
              size="icon"
              @click="showModal = false"
            >
              <X
                class="w-5 h-5"
                stroke-width="1.5"
              />
            </Button>
          </div>

          <!-- Content -->
          <div class="flex-1 overflow-hidden flex flex-col">
            <!-- Toolbar -->
            <div class="px-4 py-2 bg-muted/10 flex items-center justify-between gap-4">
              <!-- Breadcrumbs -->
              <div class="flex items-center text-sm overflow-hidden whitespace-nowrap">
                <Button 
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 text-muted-foreground hover:text-foreground"
                  @click="navigateToBreadcrumb(-1)"
                >
                  <Home
                    class="w-4 h-4"
                    stroke-width="1.5"
                  />
                </Button>
                <template
                  v-for="(crumb, index) in breadcrumbs"
                  :key="crumb.id"
                >
                  <ChevronRight
                    class="w-4 h-4 text-muted-foreground mx-0.5"
                    stroke-width="1.5"
                  />
                  <Button 
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="h-8 px-2 text-foreground font-medium truncate max-w-[120px]"
                    @click="navigateToBreadcrumb(index)"
                  >
                    {{ crumb.name }}
                  </Button>
                </template>
              </div>

              <div class="flex items-center gap-1 bg-muted/20 p-1 rounded-xl">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-lg"
                  :class="{ 'bg-background shadow-none border border-border/40': viewMode === 'grid', 'text-muted-foreground': viewMode !== 'grid' }"
                  @click="viewMode = 'grid'"
                >
                  <Grid
                    class="w-4 h-4"
                    stroke-width="1.5"
                  />
                </Button>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-7 w-7 rounded-lg"
                  :class="{ 'bg-background shadow-none border border-border/40': viewMode === 'list', 'text-muted-foreground': viewMode !== 'list' }"
                  @click="viewMode = 'list'"
                >
                  <ListIcon
                    class="w-4 h-4"
                    stroke-width="1.5"
                  />
                </Button>
              </div>
            </div>

            <!-- Main Area -->
            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
              <div
                v-if="loading"
                class="flex flex-col items-center justify-center h-full py-12"
              >
                <div class="w-8 h-8 border-4 border-primary/30 border-t-primary rounded-full animate-spin" />
                <p class="mt-4 text-xs text-muted-foreground">
                  {{ $t('media.loading') }}
                </p>
              </div>

              <div
                v-else-if="folders.length === 0 && mediaList.length === 0"
                class="flex flex-col items-center justify-center h-full py-12 text-muted-foreground"
              >
                <div class="bg-muted/50 p-4 rounded-xl mb-3">
                  <Folder
                    class="w-8 h-8 opacity-50"
                    stroke-width="1.5"
                  />
                </div>
                <p class="text-sm font-medium">
                  {{ $t('media.empty') }}
                </p>
              </div>

              <div v-else>
                <!-- Grid View -->
                <div
                  v-if="viewMode === 'grid'"
                  class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3"
                >
                  <!-- Folders -->
                  <div
                    v-for="folder in folders"
                    :key="'folder-' + folder.id"
                    class="group cursor-pointer flex flex-col items-center p-3 rounded-xl hover:bg-muted/50 transition-colors border border-transparent hover:border-border/40"
                    @dblclick="navigateToFolder(folder)"
                  >
                    <div class="w-full aspect-square bg-blue-500/10 text-blue-500 rounded-xl flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                      <Folder
                        class="w-8 h-8 fill-current"
                        stroke-width="1.5"
                      />
                    </div>
                    <span class="text-xs font-medium text-center truncate w-full px-1">{{ folder.name }}</span>
                  </div>

                  <!-- Files -->
                  <div
                    v-for="media in mediaList"
                    :key="'media-' + media.id"
                    class="group cursor-pointer relative border border-transparent rounded-xl overflow-hidden transition-colors hover:bg-muted/30"
                    :class="selectedMediaId === media.id ? 'ring-2 ring-primary ring-offset-2 bg-muted/30' : ''"
                    @click="selectMedia(media)"
                  >
                    <div class="aspect-square bg-muted/20 relative overflow-hidden rounded-xl m-1.5">
                      <img
                        v-if="media.mime_type?.startsWith('image/')"
                        :src="media.url"
                        :alt="media.name"
                        class="w-full h-full object-cover"
                      >
                      <div
                        v-else
                        class="w-full h-full flex items-center justify-center text-muted-foreground"
                      >
                        <File
                          class="w-8 h-8"
                          stroke-width="1.5"
                        />
                      </div>
                    </div>
                    <div class="px-2 pb-2 text-center">
                      <p class="text-[10px] font-medium truncate mb-0.5">
                        {{ media.name }}
                      </p>
                      <p class="text-[9px] text-muted-foreground truncate">
                        {{ formatSize(media.size) }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- List View -->
                <div
                  v-else
                  class="min-w-full inline-block align-middle"
                >
                  <div class="border border-border/40 rounded-xl overflow-hidden shadow-none">
                    <div class="overflow-x-auto min-w-0">
<table class="min-w-full">
                      <thead class="bg-muted/30 border-b border-border/40">
                        <tr>
                          <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-muted-foreground"
                          >
                            Name
                          </th>
                          <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-muted-foreground"
                          >
                            Size
                          </th>
                          <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-muted-foreground"
                          >
                            Type
                          </th>
                          <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-medium text-muted-foreground"
                          >
                            Modified
                          </th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-border/50 bg-background">
                        <!-- Folders -->
                        <tr 
                          v-for="folder in folders" 
                          :key="'folder-' + folder.id"
                          class="hover:bg-muted/30 cursor-pointer group"
                          @dblclick="navigateToFolder(folder)"
                        >
                          <td class="px-4 py-2.5 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                              <Folder
                                class="w-4 h-4 text-blue-500 fill-current"
                                stroke-width="1.5"
                              />
                              <span class="text-sm font-medium text-foreground group-hover:text-primary transition-colors">{{ folder.name }}</span>
                            </div>
                          </td>
                          <td class="px-4 py-2.5 whitespace-nowrap text-xs text-muted-foreground">
                            -
                          </td>
                          <td class="px-4 py-2.5 whitespace-nowrap text-xs text-muted-foreground">
                            Folder
                          </td>
                          <td class="px-4 py-2.5 whitespace-nowrap text-xs text-muted-foreground">
                            {{ formatDate(folder.updated_at) }}
                          </td>
                        </tr>
                        <!-- Files -->
                        <tr 
                          v-for="media in mediaList" 
                          :key="'media-' + media.id"
                          class="hover:bg-muted/30 cursor-pointer transition-colors"
                          :class="selectedMediaId === media.id ? 'bg-primary/5' : ''"
                          @click="selectMedia(media)"
                        >
                          <td class="px-4 py-2.5 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                              <div class="w-8 h-8 rounded-xl bg-muted/20 overflow-hidden flex-shrink-0 border border-border/40 shadow-none">
                                <img
                                  v-if="media.mime_type?.startsWith('image/')"
                                  :src="media.url"
                                  class="w-full h-full object-cover"
                                >
                                <File
                                  v-else
                                  class="w-full h-full p-1.5 text-muted-foreground"
                                  stroke-width="1.5"
                                />
                              </div>
                              <span
                                class="text-sm truncate max-w-[200px]"
                                :class="selectedMediaId === media.id ? 'text-primary font-medium' : 'text-foreground'"
                              >{{ media.name }}</span>
                            </div>
                          </td>
                          <td class="px-4 py-2.5 whitespace-nowrap text-xs text-muted-foreground">
                            {{ formatSize(media.size) }}
                          </td>
                          <td class="px-4 py-2.5 whitespace-nowrap text-xs text-muted-foreground">
                            {{ media.extension || 'File' }}
                          </td>
                          <td class="px-4 py-2.5 whitespace-nowrap text-xs text-muted-foreground">
                            {{ formatDate(media.updated_at) }}
                          </td>
                        </tr>
                      </tbody>
                    </table>
</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between p-4 border-t border-border/40 bg-muted/20">
            <Button
              type="button"
              variant="default"
              size="sm"
              @click="showUpload = true"
            >
              {{ $t('media.actions.uploadNew') }}
            </Button>
            <div class="flex space-x-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                @click="showModal = false"
              >
                {{ $t('media.actions.cancel') }}
              </Button>
              <Button
                type="button"
                :disabled="!selectedMediaId"
                variant="default"
                size="sm"
                @click="confirmSelection"
              >
                {{ $t('media.actions.select') }}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Upload Modal -->
    <Teleport to="body">
      <div
        v-if="showUpload"
        class="fixed inset-0 z-[100001] overflow-y-auto bg-background/80 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="showUpload = false"
      >
        <div class="bg-card rounded-xl max-w-md w-full p-6 shadow-none border border-border/40">
          <h3 class="text-lg font-semibold mb-4">
            {{ $t('media.modals.upload.title') }}
          </h3>
          <MediaUpload 
            :folder-id="currentFolderId"
            :path="path"
            :module="module"
            :constraints="constraints"
            @uploaded="handleMediaUploaded" 
          />
          <Button
            type="button"
            variant="ghost"
            class="mt-4 w-full"
            @click="showUpload = false"
          >
            {{ $t('media.actions.close') }}
          </Button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, watch, computed } from 'vue';
import api from '@/engine/api/client';
import { getResponseList } from '@/shared/utils/responseParser';
import { useToast } from '@/shared/composables/useToast';
import {
  ChevronRight,
  File,
  Folder,
  Grid,
  Home,
  ImageIcon,
  ListIcon,
  X,
} from 'lucide-vue-next';
import { Button } from '@/shared/components/ui';
import MediaUpload from './MediaUpload.vue';
import type { Media, MediaFolder, MediaConstraints } from '@/modules/Media/types/media';

const props = defineProps<{
    label?: string;
    constraints?: Partial<MediaConstraints>;
    open?: boolean;
    path?: string;
    module?: string;
}>();

const emit = defineEmits<{
    (e: 'selected', media: Media): void;
    (e: 'update:open', value: boolean): void;
}>();

const toast = useToast();

const showModal = ref(false);

// Open modal function
const openModal = () => {
    showModal.value = true
}

// Sync prop 'open' to internal state
watch(() => props.open, (newVal) => {
    if (newVal !== undefined) {
        showModal.value = newVal;
    }
}, { immediate: true });

// Sync internal state to prop 'open'
watch(showModal, (newVal) => {
    emit('update:open', newVal);
});
const showUpload = ref(false);
const loading = ref(false);


// modalRef is not used
const position = ref({ x: 0, y: 0 });
let isDragging = false;
let startPos = { x: 0, y: 0 };
let startMouse = { x: 0, y: 0 };

const startDrag = (e: MouseEvent) => {
    if (e.target instanceof HTMLElement && (e.target.tagName === 'BUTTON' || e.target.closest('button'))) return; // Don't drag if clicking buttons
    isDragging = true;
    startMouse = { x: e.clientX, y: e.clientY };
    startPos = { ...position.value };
    
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
};

const onDrag = (e: MouseEvent) => {
    if (!isDragging) return;
    const dx = e.clientX - startMouse.x;
    const dy = e.clientY - startMouse.y;
    position.value = {
        x: startPos.x + dx,
        y: startPos.y + dy
    };
};

const stopDrag = () => {
    isDragging = false;
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
};

// Reset position when re-opened
watch(showModal, (val) => {
    if (val) {
        position.value = { x: 0, y: 0 };
    }
});

// State
const viewMode = ref<'grid' | 'list'>('grid'); // 'grid' | 'list'
const currentFolderId = ref<string | null>(null);
const breadcrumbs = ref<MediaFolder[]>([]);
const folders = ref<MediaFolder[]>([]);
const mediaList = ref<Media[]>([]);
const selectedMedia = ref<Media | null>(null);

const fetchMedia = async () => {
    loading.value = true;
    try {
        const folderId = currentFolderId.value;
        const params = {
            folder_id: folderId || 'null',
            module: props.module || 'publishing',
        };
        
        // Fetch both folders and media in parallel
        const [foldersRes, mediaRes] = await Promise.all([
             api.get('/manage/folders', { params: { parent_id: folderId || 'null', module: props.module || 'publishing' } }),
             api.get('/manage/media', { params })
        ]);

        // Filter out any potential nulls
        const rawFolders = getResponseList<MediaFolder>(foldersRes.data);
        
        // Handle media pagination structure after interceptor unwrapping
        const rawMedia = getResponseList<Media>(mediaRes.data);

        folders.value = Array.isArray(rawFolders) ? rawFolders.filter(f => f && f.id) : [];
        
        // Filter media by allowed extensions
        const allowed = props.constraints?.allowedExtensions || [];
        if (allowed.length > 0) {
            mediaList.value = Array.isArray(rawMedia) ? rawMedia.filter(m => {
                if (!m || !m.id) return false;
                const ext = m.extension || m.url?.split('.').pop()?.toLowerCase();
                return typeof ext === 'string' && allowed.includes(ext);
            }) : [];
        } else {
            mediaList.value = Array.isArray(rawMedia) ? rawMedia.filter(m => m && m.id) : [];
        }
        
    } catch (error) {
        logger.error('Failed to fetch media:', error);
    } finally {
        loading.value = false;
    }
};



const selectedMediaId = computed(() => selectedMedia.value ? selectedMedia.value.id : null);

const navigateToFolder = (folder: MediaFolder) => {
    currentFolderId.value = folder.id;
    breadcrumbs.value.push(folder);
    selectedMedia.value = null;
    fetchMedia();
};

const navigateToBreadcrumb = (index: number) => {
    if (index === -1) {
        // Go to home
        breadcrumbs.value = [];
        currentFolderId.value = null;
    } else {
        // Go to specific folder
        const folder = breadcrumbs.value[index];
        if (folder) {
            breadcrumbs.value = breadcrumbs.value.slice(0, index + 1);
            currentFolderId.value = folder.id;
        }
    }
    selectedMedia.value = null;
    fetchMedia();
};

const selectMedia = (media: Media) => {
    const allowed = props.constraints?.allowedExtensions || [];
    const ext = media.extension || media.url?.split('.').pop()?.toLowerCase() || '';
    
    if (allowed.length > 0 && !allowed.includes(ext)) {
        toast.error.validation(`File type .${ext} is not allowed for this field.`);
        return;
    }
    selectedMedia.value = media;
};

const confirmSelection = () => {
    if (selectedMedia.value) {
        emit('selected', selectedMedia.value);
        showModal.value = false;
        selectedMedia.value = null;
    }
};

const handleMediaUploaded = (response: Record<string, unknown> | Media) => {
    const media = (response as Record<string, unknown>).media as Media || response as Media;
    if (media) {
        mediaList.value.unshift(media);
        showUpload.value = false;
        selectMedia(media);
    }
};

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString: string | undefined) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString();
};

// Watch for modal open to fetch media
watch(showModal, (isOpen) => {
    if (isOpen) {
        // Reset state on open if needed, or persist
        // For now, let's keep state if already opened
        if (mediaList.value.length === 0 && folders.value.length === 0) {
             fetchMedia();
        }
    }
});

// Expose for testing
defineExpose({
    openModal,
    fetchMedia,
    navigateToFolder,
    navigateToBreadcrumb,
    selectMedia,
    confirmSelection,
    closeModal: () => showModal.value = false,
    showModal,
    viewMode,
    currentFolderId,
    mediaList,
    folders,
    selectedMediaId
});
</script>

