<template>
  <div 
    class="border-l border-border/40 bg-background/95 lg:bg-transparent flex flex-col transition-[width] duration-300 overflow-hidden will-change-[width]"
    :class="[isVisible ? 'w-80 backdrop-blur-md lg:backdrop-blur-none' : 'w-0 border-l-0']"
  >
    <div
      v-if="isVisible"
      class="flex flex-col h-full w-80"
    >
      <!-- Header -->
      <div class="p-4 border-b border-border/40 flex items-center justify-between bg-transparent">
        <h3 class="font-semibold text-xs uppercase tracking-widest text-muted-foreground/80">
          {{ t('infra.fileManager.properties.title') }}
        </h3>
        <Button
          variant="ghost"
          size="icon"
          class="h-8 w-8"
          @click="togglePropertiesSidebar"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Content -->
      <div
        v-if="activeItem"
        class="flex-1 overflow-y-auto p-4 space-y-6"
      >
        <!-- Preview Section -->
        <div class="relative aspect-square rounded-xl bg-muted/5 border border-border/40 overflow-hidden flex items-center justify-center group">
          <img
            v-if="activeFile && isImage(activeFile)"
            :src="activeFile.url"
            :alt="activeFile.name"
            class="max-w-full max-h-full object-contain p-2 transition-transform duration-300 group-hover:scale-105"
          >
          <div
            v-else
            class="flex flex-col items-center gap-2 text-muted-foreground"
          >
            <FileIcon
              v-if="!isFolder(activeItem)"
              class="w-16 h-16 stroke-1"
            />
            <FolderIcon
              v-else
              class="w-16 h-16 stroke-1 fill-primary/10"
            />
            <span class="text-xs font-medium uppercase tracking-tighter">{{ activeFile?.extension || 'Folder' }}</span>
          </div>
        </div>

        <!-- Info Section -->
        <div class="space-y-4">
          <div>
            <h4
              class="text-sm font-bold mb-1 truncate"
              :title="activeItem.name"
            >
              {{ activeItem.name }}
            </h4>
            <p class="text-xs text-muted-foreground truncate">
              {{ activeItem.path }}
            </p>
          </div>

          <div class="h-px bg-border/40 w-full" />

          <div class="space-y-3">
            <div class="flex justify-between text-xs">
              <span class="text-muted-foreground">{{ t('infra.fileManager.properties.type') }}</span>
              <span class="font-medium capitalize">{{ activeFile?.extension || 'Folder' }}</span>
            </div>
            <div
              v-if="activeFile"
              class="flex justify-between text-xs"
            >
              <span class="text-muted-foreground">{{ t('infra.fileManager.properties.size') }}</span>
              <span class="font-medium">{{ formatFileSize(activeFile.size) }}</span>
            </div>
            <div class="flex justify-between text-xs">
              <span class="text-muted-foreground">{{ t('infra.fileManager.properties.modified') }}</span>
              <span class="font-medium">{{ formatDate(activeItem.updated_at) }}</span>
            </div>
          </div>

          <div class="h-px bg-border/40 w-full" />

          <!-- Actions -->
          <div class="grid grid-cols-2 gap-2">
            <Button
              v-if="!isFolder(activeItem)"
              variant="outline"
              size="sm"
              class="w-full"
              @click="downloadFile(activeItem)"
            >
              <Download class="w-3.5 h-3.5 mr-2" />
              {{ t('common.actions.download') }}
            </Button>
            <Button
              variant="outline"
              size="sm"
              class="w-full"
              :class="isFolder(activeItem) ? 'col-span-2' : ''"
              @click="copyPath(activeItem)"
            >
              <Copy class="w-3.5 h-3.5 mr-2" />
              {{ t('common.actions.copyPath') }}
            </Button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="flex-1 flex flex-col items-center justify-center p-8 text-center text-muted-foreground"
      >
        <div class="w-16 h-16 rounded-full bg-muted/10 flex items-center justify-center mb-4">
          <Info class="w-8 h-8 opacity-20" />
        </div>
        <p class="text-sm font-medium">
          {{ t('infra.fileManager.properties.select_item') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue';
import type { FileItem } from '@/modules/Core/Infra/types/file-manager';
import { useI18n } from 'vue-i18n';
import {
  Copy,
  Download,
  FileIcon,
  FolderIcon,
  Info,
  X,
} from 'lucide-vue-next';
import { Button } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { FileManagerKey } from '@/engine/keys';

const { t } = useI18n();
const toast = useToast();

const {
    activeItem,
    propertiesSidebarVisible: isVisible,
    togglePropertiesSidebar,
    formatFileSize,
    isImage,
} = inject(FileManagerKey)!;

const isFolder = (item: unknown) => {
    if (typeof item !== 'object' || item === null) return false;
    return 'children' in item || !('extension' in item);
};
const activeFile = computed(() => {
    if (activeItem.value && !isFolder(activeItem.value)) {
        return activeItem.value as FileItem;
    }
    return null;
});

const formatDate = (dateString: string) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};

const downloadFile = (file: { url?: string; name?: string }) => {
    if (file.url) {
        const link = document.createElement('a');
        link.href = file.url;
        link.download = file.name || 'download';
        link.click();
    }
};

const copyPath = async (item: { path: string }) => {
    try {
        await navigator.clipboard.writeText(item.path);
        toast.success.action(t('common.messages.clipboard.pathCopied'));
    } catch (_err) {
        toast.error.default(t('common.messages.clipboard.copyFailed'));
    }
};
</script>
