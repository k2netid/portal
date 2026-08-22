<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm item-center divide-y divide-border/40">
      <thead class="bg-transparent text-muted-foreground font-bold">
        <tr>
          <th class="px-4 py-4 text-left w-12">
            <Checkbox
              :checked="isAllSelected"
              :aria-label="$t('common.actions.selectAll')"
              @update:checked="toggleSelectAll"
            />
          </th>
          <th class="px-4 py-4 text-left w-12" />
          <th class="px-4 py-4 text-left text-xs font-semibold">
            {{ $t('infra.fileManager.sort.name') }}
          </th>
          <th class="px-4 py-4 text-left text-xs font-semibold">
            {{ $t('infra.fileManager.sort.size') }}
          </th>
          <th class="px-4 py-4 text-left text-xs font-semibold">
            {{ $t('infra.fileManager.sort.date') }}
          </th>
          <th class="px-4 py-4 text-right w-24" />
        </tr>
      </thead>
      <tbody class="divide-y divide-border/20">
        <ContextMenu
          v-for="folder in paginatedFolders"
          :key="folder.path"
        >
          <ContextMenuTrigger as-child>
            <tr 
              class="hover:bg-muted/10 cursor-pointer group transition-colors duration-200"
              :class="{ 'bg-primary/5 text-foreground font-medium': isSelected(folder.path) }"
              @click="selectItem(folder); navigateToPath(folder.path)"
              @contextmenu="selectItem(folder)"
            >
              <td
                class="px-4 py-3"
                @click.stop
              >
                <Checkbox
                  :checked="isSelected(folder.path)"
                  :aria-label="$t('common.actions.selectRow') + ': ' + folder.name"
                  @update:checked="() => toggleSelection(folder.path)"
                />
              </td>
              <td class="px-4 py-3">
                <Folder
                  class="w-5 h-5 text-muted-foreground/60"
                  stroke-width="1.5"
                />
              </td>
              <td class="px-4 py-3 font-semibold text-foreground/90">
                {{ folder.name }}
              </td>
              <td class="px-4 py-3 text-muted-foreground/60">
                —
              </td>
              <td class="px-4 py-3 text-muted-foreground/60 font-medium">
                {{ formatDate(folder.updated_at) }}
              </td>
              <td class="px-4 py-3 text-right">
                <FileActionDropdown :item="folder">
                  <Button
                    variant="ghost"
                    size="icon"
                    type="button"
                    class="h-8 w-8 opacity-0 group-hover:opacity-100 transition-opacity"
                    :aria-label="$t('infra.fileManager.actions.quickActions')"
                    @click.stop
                  >
                    <MoreVertical class="w-4 h-4" />
                  </Button>
                </FileActionDropdown>
              </td>
            </tr>
          </ContextMenuTrigger>
          <FileContextMenu :item="folder" />
        </ContextMenu>

        <ContextMenu
          v-for="file in paginatedFiles"
          :key="file.path"
        >
          <ContextMenuTrigger as-child>
            <tr 
              class="hover:bg-muted/30 cursor-pointer group transition-colors duration-200"
              :class="{ 'bg-primary/5 text-foreground font-medium': isSelected(file.path) }"
              @click="selectItem(file); $emit('preview', file)"
              @contextmenu="selectItem(file)"
            >
              <td
                class="px-4 py-3"
                @click.stop
              >
                <Checkbox
                  :checked="isSelected(file.path)"
                  :aria-label="$t('common.actions.selectRow') + ': ' + file.name"
                  @update:checked="() => toggleSelection(file.path)"
                />
              </td>
              <td class="px-4 py-3">
                <div class="w-8 h-8 rounded-lg overflow-hidden bg-muted/20 border border-border/40 flex items-center justify-center">
                  <img
                    v-if="isImage(file)"
                    :src="file.url"
                    class="w-full h-full object-cover"
                  >
                  <Video
                    v-else-if="isVideo(file)"
                    class="w-4 h-4 text-muted-foreground"
                  />
                  <FileText
                    v-else
                    class="w-4 h-4 text-muted-foreground"
                  />
                </div>
              </td>
              <td class="px-4 py-3 font-semibold text-foreground/90">
                {{ file.name }}
              </td>
              <td class="px-4 py-3 text-muted-foreground/60 font-medium">
                {{ formatFileSize(file.size) }}
              </td>
              <td class="px-4 py-3 text-muted-foreground/60 font-medium">
                {{ formatDate(file.updated_at) }}
              </td>
              <td class="px-4 py-3 text-right">
                <FileActionDropdown
                  :item="file"
                  @preview="$emit('preview', file)"
                >
                  <Button
                    variant="ghost"
                    size="icon"
                    type="button"
                    class="h-8 w-8 opacity-0 group-hover:opacity-100 transition-opacity"
                    :aria-label="$t('infra.fileManager.actions.quickActions')"
                    @click.stop
                  >
                    <MoreVertical class="w-4 h-4" />
                  </Button>
                </FileActionDropdown>
              </td>
            </tr>
          </ContextMenuTrigger>
          <FileContextMenu
            :item="file"
            @preview="$emit('preview', file)"
          />
        </ContextMenu>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { inject } from 'vue';
import {
  FileText,
  Folder,
  MoreVertical,
  Video,
} from 'lucide-vue-next';
import { 
    Checkbox, 
    Button, 
    ContextMenu, 
    ContextMenuTrigger
} from '@/shared/components/ui';
import FileContextMenu from './FileContextMenu.vue';
import FileActionDropdown from './FileActionDropdown.vue';
import type { FileItem, FolderItem } from '@/modules/Core/Infra/types/file-manager';
import { FileManagerKey } from '@/engine/keys';

defineEmits<{
    (e: 'preview', item: FileItem): void;
}>();

const {
    paginatedFolders,
    paginatedFiles,
    selectedItems,
    isAllSelected,
    navigateToPath,
    toggleSelection,
    toggleSelectAll,
    formatFileSize,
    isImage,
    isVideo,
    selectItem
} = inject(FileManagerKey)!;

const formatDate = (dateString: string | null | undefined) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const isSelected = (path: string) => selectedItems.value.some((item: FileItem | FolderItem) => item.path === path);
</script>
