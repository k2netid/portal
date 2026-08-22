<template>
  <Popover v-model:open="isOpen">
    <PopoverTrigger as-child>
      <slot />
    </PopoverTrigger>
    <PopoverContent
      class="w-48 p-1 bg-popover border border-border/60 shadow-lg rounded-xl"
      align="end"
    >
      <div class="flex flex-col gap-0.5">
        <!-- Open -->
        <Button 
          variant="ghost" 
          size="sm" 
          class="w-full justify-start text-[13px] h-9 px-3 rounded-lg hover:bg-accent hover:text-accent-foreground"
          @click="handleAction('open')"
        >
          <FolderOpen
            v-if="isFolder(item)"
            class="w-4 h-4 mr-2.5 opacity-70"
          />
          <Eye
            v-else
            class="w-4 h-4 mr-2.5 opacity-70"
          />
          {{ t('infra.fileManager.actions.open') }}
        </Button>

        <!-- Download -->
        <Button 
          v-if="!isFolder(item)"
          variant="ghost" 
          size="sm" 
          class="w-full justify-start text-[13px] h-9 px-3 rounded-lg hover:bg-accent hover:text-accent-foreground"
          @click="handleAction('download')"
        >
          <Download class="w-4 h-4 mr-2.5 opacity-70" />
          {{ t('infra.fileManager.actions.download') }}
        </Button>

        <div class="h-px bg-border/40 my-1 mx-1" />

        <!-- Copy Path -->
        <Button 
          variant="ghost" 
          size="sm" 
          class="w-full justify-start text-[13px] h-9 px-3 rounded-lg hover:bg-accent hover:text-accent-foreground"
          @click="handleAction('copyPath')"
        >
          <Link class="w-4 h-4 mr-2.5 opacity-70" />
          {{ t('infra.fileManager.actions.copyPath') }}
        </Button>

        <!-- Copy URL -->
        <Button 
          v-if="!isFolder(item)"
          variant="ghost" 
          size="sm" 
          class="w-full justify-start text-[13px] h-9 px-3 rounded-lg hover:bg-accent hover:text-accent-foreground"
          @click="handleAction('copyUrl')"
        >
          <Copy class="w-4 h-4 mr-2.5 opacity-70" />
          {{ t('infra.fileManager.actions.copyUrl') }}
        </Button>

        <div class="h-px bg-border/40 my-1 mx-1" />

        <!-- Copy/Move -->
        <Button 
          variant="ghost" 
          size="sm" 
          class="w-full justify-start text-[13px] h-9 px-3 rounded-lg hover:bg-accent hover:text-accent-foreground"
          @click="handleAction('copy')"
        >
          <CopyIcon class="w-4 h-4 mr-2.5 opacity-70" />
          {{ t('infra.fileManager.actions.copy') }}
        </Button>

        <div class="h-px bg-border/40 my-1 mx-1" />

        <!-- Delete -->
        <Button 
          variant="ghost" 
          size="sm" 
          class="w-full justify-start text-[13px] h-9 px-3 rounded-lg text-destructive hover:bg-destructive/10 hover:text-destructive"
          @click="handleAction('delete')"
        >
          <Trash2 class="w-4 h-4 mr-2.5 opacity-70" />
          {{ t('infra.fileManager.actions.delete') }}
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Copy,
  CopyIcon,
  Download,
  Eye,
  FolderOpen,
  Link,
  Trash2,
} from 'lucide-vue-next';
import { 
    Button, 
    Popover, 
    PopoverTrigger, 
    PopoverContent 
} from '@/shared/components/ui';
import { FileManagerKey } from '@/engine/keys';

import type { FileItem, FolderItem } from '@/modules/Core/Infra/types/file-manager';

const { item } = defineProps<{
    item: FileItem | FolderItem;
}>();

const emit = defineEmits(['preview']);

const { t } = useI18n();
const isOpen = ref(false);

const {
    navigateToPath,
    copyToClipboard,
    deleteItem,
    copyPath,
    copyUrl,
    downloadFile
} = inject(FileManagerKey)!;

const isFolder = (item: FileItem | FolderItem): item is FolderItem => 'children' in item || !('extension' in item);

const handleAction = async (action: string) => {
    isOpen.value = false;
    
    switch (action) {
        case 'open':
            if (isFolder(item)) navigateToPath(item.path);
            else emit('preview', item as FileItem);
            break;
        case 'download':
            if (!isFolder(item)) downloadFile(item);
            break;
        case 'copyPath':
            await copyPath(item);
            break;
        case 'copyUrl':
            if (!isFolder(item)) await copyUrl(item);
            break;
        case 'copy':
            copyToClipboard([item], 'copy');
            break;
        case 'delete':
            await deleteItem(item);
            break;
    }
};
</script>
