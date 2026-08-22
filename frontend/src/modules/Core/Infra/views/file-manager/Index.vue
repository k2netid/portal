<template>
  <div class="file-manager-container space-y-6 min-w-0 max-w-full">
    <PageHeader
      borderless
      :title="t('infra.fileManager.title')"
      :subtitle="t('infra.fileManager.description')"
    >
      <template #actions>
        <div class="flex items-center space-x-3">
          <Button 
            variant="outline"
            @click="showCreateFolderModal = true"
          >
            <FolderPlus class="w-4 h-4 mr-2" />
            {{ t('infra.fileManager.actions.newFolder') }}
          </Button>
          <Button
            type="button"
            @click="showUploadModal = true"
          >
            <Upload class="w-4 h-4 mr-2" />
            {{ t('infra.fileManager.actions.upload') }}
          </Button>
        </div>
      </template>
    </PageHeader>

    <div class="min-w-0 space-y-6 p-4 sm:p-6">
      <div class="flex gap-6 relative">
        <!-- Sidebar Navigation (Left) -->
        <FileSidebar 
          :class="[
            'shrink-0 z-[40] border-r border-border/40 lg:border-r-0',
            fm.sidebarCollapsed.value 
              ? 'hidden lg:block w-0' 
              : 'fixed lg:relative inset-y-0 left-0 w-72 lg:w-72 bg-white lg:bg-transparent shadow-xl lg:shadow-none lg:border-r lg:border-border/40',
            fm.isMounted.value ? 'transition-colors duration-300' : ''
          ]" 
        />

        <!-- Sidebar Backdrop (Mobile Overlay) -->
        <div 
          v-if="!fm.sidebarCollapsed.value" 
          class="fixed inset-0 z-[35] bg-black/50 backdrop-blur-sm lg:hidden h-full w-full"
          @click="fm.toggleSidebar"
        />

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0 flex flex-col gap-4 relative">
          <!-- Floating Toggle Button -->
          <div class="absolute -left-9 top-7 -translate-y-1/2 z-[45] hidden lg:block">
            <Button
              variant="outline"
              size="icon"
              :aria-label="t('navigation.toggleSidebar')"
              type="button"
              class="h-7 w-7 rounded-full bg-background border border-border/60 shadow-sm hover:shadow-md hover:bg-muted transition-colors flex items-center justify-center p-0"
              @click="fm.toggleSidebar"
            >
              <ChevronLeft
                v-if="!fm.sidebarCollapsed.value"
                class="w-3.5 h-3.5 text-muted-foreground"
              />
              <ChevronRight
                v-else
                class="w-3.5 h-3.5 text-muted-foreground"
              />
            </Button>
          </div>

          <!-- Toolbar & Content Card -->
          <div class="bg-card border border-border/40 rounded-xl overflow-hidden shadow-none flex flex-col min-h-[500px]">
            <FileToolbar 
              class="py-2.5 px-4 bg-transparent border-b border-border/40" 
              @new-folder="showCreateFolderModal = true"
              @upload="showUploadModal = true"
            />
                      
            <div class="flex-1 overflow-hidden relative group">
              <ContextMenu>
                <ContextMenuTrigger as-child>
                  <div class="h-full overflow-y-auto custom-scrollbar">
                    <div v-if="fm.showTrashView.value" class="h-full">
                      <FileTrashView />
                    </div>

                    <div v-else-if="fm.loading.value" class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-background/60 backdrop-blur-[2px]">
                      <Spinner class="w-10 h-10 text-primary" />
                      <p class="mt-4 text-xs font-bold text-primary animate-pulse uppercase tracking-widest leading-none">
                        {{ t('infra.fileManager.messages.loading') }}
                      </p>
                    </div>

                    <div v-else-if="!fm.loading.value && (fm.paginatedFolders.value.length === 0 && fm.paginatedFiles.value.length === 0)" class="flex flex-col items-center justify-center h-full p-12 text-center">
                      <div class="w-20 h-20 rounded-full bg-muted/20 flex items-center justify-center mb-6">
                        <FolderPlus class="w-8 h-8 text-muted-foreground/30" stroke-width="1.5" />
                      </div>
                      <h3 class="text-lg font-bold text-foreground/90">
                        {{ t('infra.fileManager.messages.noFiles') }}
                      </h3>
                    </div>

                    <div v-else class="p-0">
                      <FileGridView v-if="fm.viewMode.value === 'grid'" @preview="openPreview" />
                      <FileListView v-else @preview="openPreview" />
                    </div>
                  </div>
                </ContextMenuTrigger>
                <ContextMenuContent class="w-56">
                  <ContextMenuItem @click="showCreateFolderModal = true">
                    <FolderPlus class="w-4 h-4 mr-2" />
                    {{ t('infra.fileManager.actions.newFolder') }}
                  </ContextMenuItem>
                  <ContextMenuItem @click="showUploadModal = true">
                    <Upload class="w-4 h-4 mr-2" />
                    {{ t('infra.fileManager.actions.upload') }}
                  </ContextMenuItem>
                  <ContextMenuSeparator v-if="fm.clipboard.value.items.length > 0" />
                  <ContextMenuItem v-if="fm.clipboard.value.items.length > 0" @click="fm.pasteFromClipboard(fm.currentPath.value)">
                    <ClipboardPaste class="w-4 h-4 mr-2" />
                    {{ t('infra.fileManager.actions.paste') }}
                  </ContextMenuItem>
                  <ContextMenuSeparator />
                  <ContextMenuItem @click="fm.fetchCurrentPath()">
                    <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                    {{ t('common.actions.refresh') }}
                  </ContextMenuItem>
                </ContextMenuContent>
              </ContextMenu>
            </div>
          </div>

          <!-- Pagination -->
          <div class="mt-2">
            <Pagination
              embedded
              v-if="fm.totalItems.value > 0"
              v-model:current-page="fm.currentPage.value"
              v-model:per-page="fm.itemsPerPage.value"
              :total-items="fm.totalItems.value"
              @page-change="() => fm.fetchCurrentPath()"
            />
          </div>
        </div>

        <!-- Properties Sidebar -->
        <FilePropertiesSidebar class="h-full" />
      </div>

      <!-- Modals -->
      <FilePreviewModal v-if="previewFile" :file="previewFile" @close="previewFile = null" />
      <FileUploadModal v-if="showUploadModal" :path="fm.currentPath.value" @close="showUploadModal = false" @uploaded="fm.fetchCurrentPath()" />
      <CreateFolderModal v-if="showCreateFolderModal" :path="fm.currentPath.value" @close="showCreateFolderModal = false" @created="fm.fetchCurrentPath()" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';
import { ref, onMounted, onUnmounted, provide } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  ChevronLeft,
  ChevronRight,
  ClipboardPaste,
  FolderPlus,
  RefreshCw,
  Upload,
} from 'lucide-vue-next';
import { 
    Button, 
    Pagination,
    ContextMenu,
    ContextMenuTrigger,
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuSeparator,
    Spinner
} from '@/shared/components/ui';

import { useFileManager } from '@/modules/Core/Infra/composables/useFileManager';
import FileSidebar from '@/modules/Core/Infra/components/file-manager/FileSidebar.vue';
import FileToolbar from '@/modules/Core/Infra/components/file-manager/FileToolbar.vue';
import FilePropertiesSidebar from '@/modules/Core/Infra/components/file-manager/FilePropertiesSidebar.vue';
import FileGridView from '@/modules/Core/Infra/components/file-manager/FileGridView.vue';
import FileListView from '@/modules/Core/Infra/components/file-manager/FileListView.vue';
import FileTrashView from '@/modules/Core/Infra/components/file-manager/FileTrashView.vue';
import FilePreviewModal from '@/modules/Core/Infra/components/file-manager/FilePreviewModal.vue';
import FileUploadModal from '@/modules/Core/Infra/components/file-manager/FileUploadModal.vue';
import CreateFolderModal from '@/modules/Core/Infra/components/file-manager/CreateFolderModal.vue';
import type { FileItem } from '@/modules/Core/Infra/types/file-manager';
import { FileManagerKey } from '@/engine/keys';

const { t } = useI18n();

const fm = useFileManager(); 

provide(FileManagerKey, fm);

const showUploadModal = ref(false);
const showCreateFolderModal = ref(false);
const previewFile = ref<FileItem | null>(null);

const MOBILE_BREAKPOINT = 1024;

const syncSidebarForViewport = () => {
    if (typeof window === "undefined") return;
    if (window.innerWidth < MOBILE_BREAKPOINT) {
        fm.sidebarCollapsed.value = true;
    }
};

const openPreview = (file: FileItem) => {
    previewFile.value = file;
};

onMounted(() => {
    syncSidebarForViewport();
    if (typeof window !== 'undefined') {
        window.addEventListener('resize', syncSidebarForViewport);
    }

    fm.isMounted.value = true;
    fm.fetchAllFolders();
    fm.fetchCurrentPath();
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('resize', syncSidebarForViewport);
    }
});
</script>
