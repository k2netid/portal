<template>
  <div class="console-page min-w-0 max-w-full">
    <PageHeader
      borderless
      :title="t('media.title')"
      :subtitle="t('media.description')"
    >
      <template #actions>
        <Button
          variant="outline"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="showFolderModal = true"
        >
          <FolderPlus
            data-icon="inline-start"
            class="size-4 shrink-0"
          />
          {{ t('media.newFolder') }}
        </Button>
        <Button
          type="button"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="showUploadModal = true"
        >
          <Plus
            data-icon="inline-start"
            class="size-4 shrink-0"
          />
          {{ t('media.upload') }}
        </Button>
      </template>
    </PageHeader>

<div class="min-w-0 space-y-6">
    <div class="flex gap-6 relative">
      <!-- Sidebar -->
      <MediaSidebar 
        :class="[
          'shrink-0 z-[40]',
          sidebarCollapsed 
            ? 'hidden lg:block w-0' 
            : 'fixed lg:relative inset-y-0 left-0 w-72 lg:w-72 bg-card shadow-xl lg:shadow-none border-r border-border lg:border-r',
          'transition-colors duration-300'
        ]"
      />

      <!-- Sidebar Backdrop (Mobile) -->
      <div 
        v-if="!sidebarCollapsed" 
        class="fixed inset-0 z-[35] bg-black/50 backdrop-blur-sm lg:hidden h-full w-full"
        @click="sidebarCollapsed = true"
      />

      <!-- Main Content -->
      <div class="flex-1 min-w-0 flex flex-col gap-4 relative">
        <MediaStats
          :stats="statistics"
        />
        <!-- Floating Toggle Button (Desktop) -->
        <div class="absolute -left-9 top-7 -translate-y-1/2 z-[45] hidden lg:block">
          <Button
            variant="outline"
            size="icon"
            :aria-label="t('navigation.toggleSidebar')"
            type="button"
            class="h-7 w-7 rounded-full bg-background border border-border/60 shadow-sm hover:shadow-md hover:bg-muted transition-colors flex items-center justify-center p-0"
            @click="sidebarCollapsed = !sidebarCollapsed"
          >
            <ChevronLeft
              v-if="!sidebarCollapsed"
              class="w-3.5 h-3.5 text-muted-foreground"
            />
            <ChevronRight
              v-else
              class="w-3.5 h-3.5 text-muted-foreground"
            />
          </Button>
        </div>

        <!-- Toolbar & Content Card -->
        <div class="flex flex-col min-h-[500px] overflow-hidden rounded-xl border border-border/50 bg-card shadow-none">
          <!-- Toolbar -->
          <MediaToolbar class="bg-transparent border-b border-border/40 py-2" />

          <!-- Content Area -->
          <div class="flex-1 overflow-hidden relative group">
            <div class="h-full overflow-y-auto custom-scrollbar p-0">
              <div
                v-if="loading"
                class="p-12 text-center h-full flex flex-col items-center justify-center"
              >
                <Loader2 class="w-8 h-8 animate-spin text-primary mb-4" />
                <p class="text-muted-foreground">
                  {{ t('media.loading') }}
                </p>
              </div>
        
              <ContextMenu
                v-else-if="mediaList.length === 0 && currentFolders.length === 0"
                class="h-full"
              >
                <ContextMenuTrigger class="h-full">
                  <EmptyState
                    :title="t('media.empty')"
                    :description="t('media.file_manager.help.sections.navigation.content')"
                    :icon="FolderPlus"
                  />
                </ContextMenuTrigger>
                <ContextMenuContent>
                  <template v-if="!isTrashMode">
                    <ContextMenuItem @click="showFolderModal = true">
                      <FolderPlus class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                      {{ t('media.newFolder') }}
                    </ContextMenuItem>
                    <ContextMenuItem @click="showUploadModal = true">
                      <Plus class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                      {{ t('media.upload') }}
                    </ContextMenuItem>
                    <ContextMenuSeparator />
                    <ContextMenuItem @click="fetchMedia(); fetchFolders();">
                      <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                      {{ t('media.actions.refresh') }}
                    </ContextMenuItem>
                  </template>
                  <template v-else>
                    <ContextMenuItem @click="emptyTrash">
                      <Trash2 class="w-4 h-4 text-destructive/70 transition-colors" />
                      {{ t('media.emptyTrash') }}
                    </ContextMenuItem>
                  </template>
                </ContextMenuContent>
              </ContextMenu>

              <div v-else>
                <MediaGridView v-if="viewMode === 'grid'" />
                <MediaListView v-else />
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <Pagination
          v-if="pagination && pagination.total > 0"
          v-model:per-page="pagination.per_page"
          :current-page="pagination.current_page"
          :total-items="pagination.total"
          :show-per-page="true"
          embedded
          class="mt-2"
          @page-change="changePage"
        />
      </div>

      <!-- Properties Panel (Right) -->
      <MediaPropertiesPanel />
    </div>

    <!-- Modals -->
    <MediaUploadModal
      v-if="showUploadModal"
      :folder-id="selectedFolder ? String(selectedFolder) : null"
      @close="showUploadModal = false"
      @uploaded="handleMediaUploaded"
    />



    <MediaViewModal
      v-if="showViewModal && viewingMedia"
      :media="viewingMedia"
      @close="showViewModal = false"
      @edit="editMedia"
      @delete="deleteMedia"
    />

    <FolderModal
      v-if="showFolderModal"
      @close="showFolderModal = false"
      @created="handleFolderCreated"
    />

    <MoveToFolderModal
      v-if="showMoveFolderModal"
      :folders="folders"
      @close="showMoveFolderModal = false"
      @moved="handleMoveToFolder"
    />

    <BulkUpdateAltModal
      v-if="showUpdateAltModal"
      :selected-count="selectedMedia.length"
      :processing="bulkProcessing"
      @close="showUpdateAltModal = false"
      @submit="handleUpdateAltText"
    />

    <div
      v-if="bulkProcessing"
      class="fixed bottom-4 right-4 bg-card border border-border/40 rounded-xl p-4 w-80 z-50 shadow-none"
    >
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-foreground">{{ t('media.modals.bulk.processing') }}</span>
        <span class="text-sm text-muted-foreground">{{ bulkProgress }}%</span>
      </div>
      <div class="w-full bg-muted rounded-full h-2">
        <div
          class="bg-indigo-600 h-2 rounded-full"
          :style="{ width: bulkProgress + '%' }"
        />
      </div>
    </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { EmptyState } from '@/shared/components/feedback';

import { PageHeader } from '@/shared/components/shell';
import { ref, onMounted, onUnmounted, provide } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
import {
  ChevronLeft,
  ChevronRight,
  FolderPlus,
  Loader2,
  Plus,
  RefreshCw,
  Trash2,
} from 'lucide-vue-next';
import { 
    Button, 
    Pagination,
    ContextMenu,
    ContextMenuTrigger,
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuSeparator
} from '@/shared/components/ui';
import MediaUploadModal from '@/modules/Media/components/picker/MediaUploadModal.vue';
import MediaViewModal from '@/modules/Media/components/picker/MediaViewModal.vue';
import FolderModal from '@/modules/Media/components/picker/FolderModal.vue';
import MoveToFolderModal from '@/modules/Media/components/picker/MoveToFolderModal.vue';
import BulkUpdateAltModal from '@/modules/Media/components/picker/BulkUpdateAltModal.vue';

// Composables & Sub-components
import { useMediaManager } from '@/modules/Media/composables/useMediaManager';
import MediaStats from '@/modules/Media/components/picker/MediaStats.vue';
import MediaSidebar from '@/modules/Media/components/picker/MediaSidebar.vue';
import MediaToolbar from '@/modules/Media/components/picker/MediaToolbar.vue';
import MediaPropertiesPanel from '@/modules/Media/components/picker/MediaPropertiesPanel.vue';
import MediaGridView from '@/modules/Media/components/picker/MediaGridView.vue';
import MediaListView from '@/modules/Media/components/picker/MediaListView.vue';

import { MediaManagerKey } from '@/engine/keys';

const mediaManager = useMediaManager();
const {
    viewMode,
    loading,
    mediaList,
    folders,
    currentFolders,
    isTrashMode,
    selectedFolder,
    selectedMedia,
    pagination,
    statistics,
    bulkProcessing,
    bulkProgress,
    fetchMedia,
    fetchStatistics,
    fetchFolders,
    fetchTags,
    fetchFilters,
    handleBulkAction,
    editMedia,
    deleteMedia,
    emptyTrash,
    // Modal State
    sidebarCollapsed,
    showUploadModal,
    showViewModal,
    showFolderModal,
    showMoveFolderModal,
    showUpdateAltModal,
    viewingMedia,
} = mediaManager;

provide(MediaManagerKey, mediaManager);

const isReady = ref(false);

const MOBILE_BREAKPOINT = 1024;

const syncSidebarForViewport = () => {
    if (typeof window === 'undefined') return;
    if (window.innerWidth < MOBILE_BREAKPOINT) {
        sidebarCollapsed.value = true;
    }
};


const handleMoveToFolder = (folderId: string | null) => {
    handleBulkAction('move', { folderId: folderId || null });
    showMoveFolderModal.value = false;
};

const handleUpdateAltText = (altText: string) => {
    handleBulkAction('update_alt', { altText });
    showUpdateAltModal.value = false;
};

const handleMediaUploaded = () => {
    fetchMedia();
    showUploadModal.value = false;
};


const handleFolderCreated = () => {
    fetchFolders();
    showFolderModal.value = false;
};


const changePage = (page: number) => {
    if (pagination.value) {
        pagination.value.current_page = page;
        fetchMedia();
    }
};

onMounted(async () => {
    syncSidebarForViewport();
    if (typeof window !== 'undefined') {
        window.addEventListener('resize', syncSidebarForViewport);
    }

    fetchMedia();
    fetchFolders();
    fetchTags();
    fetchFilters();
    fetchStatistics();
    setTimeout(() => { isReady.value = true; }, 100);
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('resize', syncSidebarForViewport);
    }
});
</script>
