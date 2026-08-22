<template>
  <Dialog
    :open="!!file"
    @update:open="(v) => !v && $emit('close')"
  >
    <DialogContent class="console-dialog-md max-w-4xl p-0 overflow-hidden bg-background border border-border/40 shadow-2xl rounded-2xl transition-colors duration-300">
      <!-- Header bar for consistency -->
      <div class="p-4 border-b border-border/40 flex justify-between items-center bg-muted/10">
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-primary/10 text-primary">
            <FileText
              v-if="file && !isImage(file) && !isVideo(file)"
              class="w-4 h-4"
            />
            <ImageIcon
              v-else-if="file && isImage(file)"
              class="w-4 h-4"
            />
            <VideoIcon
              v-else
              class="w-4 h-4"
            />
          </div>
          <span class="font-bold text-foreground truncate max-w-xs md:max-w-md">{{ file?.name }}</span>
        </div>
        <!-- Close Button -->
        <Button 
          variant="ghost" 
          size="icon" 
          type="button" 
          class="text-muted-foreground hover:text-foreground rounded-full h-8 w-8 transition-colors" 
          @click="$emit('close')"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Preview Body -->
      <div class="relative flex flex-col items-center justify-center bg-muted/20 min-h-[50vh] max-h-[70vh] overflow-hidden group">
        <!-- Image Preview -->
        <div
          v-if="file && isImage(file)"
          class="relative w-full h-full flex items-center justify-center p-4"
        >
          <img
            :src="file.url"
            :alt="file.name"
            class="max-w-full max-h-full object-contain rounded-md shadow-lg transition-transform duration-500 hover:scale-[1.01]"
          >
        </div>
                
        <!-- Video Preview -->
        <div
          v-else-if="file && isVideo(file)"
          class="relative w-full h-full flex items-center justify-center p-4"
        >
          <video
            :src="file.url"
            controls
            autoplay
            class="max-w-full max-h-full rounded-md shadow-lg"
          >
            Your browser does not support the video tag.
          </video>
        </div>
                
        <!-- Other File Types -->
        <div
          v-else-if="file"
          class="flex flex-col items-center justify-center text-muted-foreground/40 py-20"
        >
          <div class="w-32 h-32 rounded-3xl bg-primary/5 flex items-center justify-center mb-8 border border-primary/10 shadow-inner">
            <FileText
              class="w-16 h-16 text-primary/40"
              stroke-width="1"
            />
          </div>
          <p class="text-xl font-bold tracking-tight text-foreground/80">
            {{ file.name }}
          </p>
          <p class="text-sm mt-2 uppercase tracking-[0.2em] font-bold text-muted-foreground/50">
            {{ file.extension }} file
          </p>
                    
          <Button
            variant="outline"
            class="mt-8 rounded-xl border-border/60 hover:bg-accent/10 text-foreground transition-colors"
            @click="downloadFile(file)"
          >
            <Download class="w-4 h-4 mr-2" />
            Download to view
          </Button>
        </div>
      </div>

      <!-- Bottom Info Bar -->
      <div
        v-if="file"
        class="p-6 bg-background text-foreground flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-t border-border/40"
      >
        <div class="space-y-1.5">
          <div class="flex items-center gap-3 text-xs font-bold text-muted-foreground/60 uppercase tracking-widest">
            <span class="bg-primary/5 text-primary px-2.5 py-1 rounded-md border border-primary/10">{{ file.extension }}</span>
            <span>•</span>
            <span>{{ formatFileSize(file.size) }}</span>
            <span>•</span>
            <span>{{ formatDate(file.updated_at) }}</span>
          </div>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
          <Button 
            variant="outline" 
            class="flex-1 md:flex-none justify-center rounded-xl border-border/60 hover:bg-accent/10 text-foreground font-bold h-10 px-5 transition-colors" 
            @click="copyUrl(file)"
          >
            <Copy class="w-4 h-4 mr-2" />
            Copy Link
          </Button>
          <Button 
            class="flex-1 md:flex-none justify-center rounded-xl bg-primary hover:bg-primary/90 border-none text-primary-foreground font-bold h-10 px-5 shadow-lg shadow-primary/20 transition-[background-color,transform] active:scale-[0.98]" 
            @click="downloadFile(file)"
          >
            <Download class="w-4 h-4 mr-2" />
            Download
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { inject } from 'vue';
import {
  Copy,
  Download,
  FileText,
  ImageIcon,
  VideoIcon,
  X,
} from 'lucide-vue-next';
import { 
    Dialog, 
    DialogContent, 
    Button 
} from '@/shared/components/ui';
import { FileManagerKey } from '@/engine/keys';
import type { FileItem } from '@/modules/Core/Infra/types/file-manager';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/shared/composables/useToast';

defineProps<{
    file: FileItem | null;
}>();

defineEmits(['close']);

const {
    formatFileSize,
    isImage,
    isVideo
} = inject(FileManagerKey)!;

const { t } = useI18n();
const toast = useToast();

const formatDate = (dateString: string | null | undefined) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const copyUrl = async (file: FileItem) => {
    if (file.url) {
        try {
            await navigator.clipboard.writeText(file.url);
            toast.success.urlCopied();
        } catch {
            toast.error.default(t('infra.fileManager.messages.copy_failed'));
        }
    }
};

const downloadFile = (file: FileItem) => {
    if (file.url) {
        const link = document.createElement('a');
        link.href = file.url;
        link.download = file.name;
        link.click();
    }
};
</script>
