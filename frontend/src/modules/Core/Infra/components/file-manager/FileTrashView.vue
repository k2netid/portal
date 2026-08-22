<template>
  <div class="bg-transparent p-4 shadow-none h-full overflow-y-auto">
    <div class="flex items-center justify-between mb-6 pb-4 border-b">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-destructive/10 flex items-center justify-center">
          <Trash2
            class="w-5 h-5 text-destructive"
            stroke-width="2"
          />
        </div>
        <div>
          <h2 class="text-lg font-bold tracking-tight">
            {{ $t('infra.fileManager.trash.title') }}
          </h2>
          <p class="text-xs font-medium text-muted-foreground">
            {{ trashItems.length }} items
          </p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="ghost"
          size="sm"
          type="button"
          :disabled="trashLoading"
          class="h-9 border-border/40"
          @click="fetchTrash"
        >
          <RotateCcw
            class="w-4 h-4 mr-2"
            :class="{ 'animate-spin': trashLoading }"
          />
          {{ $t('infra.fileManager.trash.refresh') }}
        </Button>
        <Button
          variant="destructive"
          size="sm"
          type="button"
          :disabled="trashItems.length === 0"
          class="h-9 px-4 rounded-lg"
          @click="emptyTrash"
        >
          <Trash2 class="w-4 h-4 mr-2" />
          {{ $t('infra.fileManager.trash.empty') }}
        </Button>
      </div>
    </div>
        
    <div
      v-if="trashLoading"
      class="flex flex-col items-center justify-center py-24 text-muted-foreground"
    >
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary mb-4" />
      <p class="text-sm font-medium animate-pulse">
        {{ $t('infra.fileManager.trash.scanning') }}
      </p>
    </div>
        
    <div
      v-else-if="trashItems.length === 0"
      class="flex flex-col items-center justify-center py-24 text-center"
    >
      <div class="w-20 h-20 rounded-full bg-muted/20 flex items-center justify-center mb-6">
        <Trash2
          class="w-10 h-10 text-muted-foreground/30"
          stroke-width="1.5"
        />
      </div>
      <h3 class="text-lg font-bold text-foreground/90">
        {{ $t('infra.fileManager.trash.emptyTitle') }}
      </h3>
      <p class="text-sm text-muted-foreground max-w-[280px] mt-2">
        {{ $t('infra.fileManager.trash.emptyDescription') }}
      </p>
    </div>
        
    <div
      v-else
      class="grid grid-cols-[repeat(auto-fill,minmax(140px,1fr))] gap-4"
    >
      <div
        v-for="item in trashItems"
        :key="item.path"
        class="group relative bg-background border border-border/40 rounded-xl overflow-hidden hover:border-primary/30 transition-colors duration-300 hover:bg-accent/5"
      >
        <div class="aspect-square flex flex-col items-center justify-center bg-muted/5 group-hover:bg-muted/10 transition-colors relative">
          <Folder
            v-if="item.type === 'folder'"
            class="w-10 h-10 text-primary/70"
          />
          <FileText
            v-else
            class="w-10 h-10 text-muted-foreground/50"
          />
                    
          <div class="absolute top-2 right-2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <Button
              variant="secondary"
              size="icon"
              class="h-7 w-7 rounded-md shadow-sm"
              @click="restoreTrashItem(item)"
            >
              <RotateCcw class="w-3.5 h-3.5" />
            </Button>
            <Button
              variant="destructive"
              size="icon"
              class="h-7 w-7 rounded-md shadow-sm"
              @click="deleteFromTrashPermanent(item)"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </Button>
          </div>
        </div>
        <div class="p-3 border-t bg-card/50">
          <p
            class="text-xs font-bold truncate text-foreground/90 text-center"
            :title="item.name"
          >
            {{ item.name }}
          </p>
          <p class="text-[9px] text-muted-foreground text-center mt-1 uppercase font-bold tracking-tight opacity-60">
            {{ item.type }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject, onMounted } from 'vue';
import {
  FileText,
  Folder,
  RotateCcw,
  Trash2,
} from 'lucide-vue-next';
import { 
    Button,
} from '@/shared/components/ui';
import { FileManagerKey } from '@/engine/keys';

const {
    trashItems,
    trashLoading,
    fetchTrash,
    restoreTrashItem,
    emptyTrash,
    deleteFromTrashPermanent
} = inject(FileManagerKey)!;

onMounted(() => {
    fetchTrash();
});
</script>
