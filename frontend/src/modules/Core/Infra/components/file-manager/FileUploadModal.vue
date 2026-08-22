<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent
      class="console-dialog-lg"
      @dragover.prevent
      @drop.prevent
    >
      <DialogHeader>
        <DialogTitle>{{ $t('infra.fileManager.modals.upload.title') }}</DialogTitle>
        <DialogDescription>
          {{ $t('infra.fileManager.modals.upload.placeholder') }}
        </DialogDescription>
      </DialogHeader>

      <div class="py-4">
        <!-- Drag & Drop Area -->
        <div
          :class="[
            'border-2 border-dashed rounded-2xl p-10 text-center transition-colors cursor-pointer group/upload',
            isDragging ? 'border-primary bg-primary/10' : 'border-muted-foreground/25 hover:border-primary/50 hover:bg-muted/5'
          ]"
          @drop.prevent="handleDrop"
          @dragover.prevent
          @dragenter.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @click="triggerFileSelect"
        >
          <input
            ref="fileInput"
            type="file"
            multiple
            class="hidden"
            @change="handleFileSelect"
          >
          <CloudUpload
            class="mx-auto h-12 w-12 text-muted-foreground opacity-50 mb-4 group-hover/upload:text-primary transition-colors"
            stroke-width="1.5"
          />
          <div class="text-sm text-muted-foreground">
            <span class="text-primary font-bold group-hover/upload:underline">
              {{ $t('infra.fileManager.modals.upload.clickToUpload') }}
            </span>
            {{ $t('infra.fileManager.modals.upload.dragAndDrop') }}
          </div>
          <p class="mt-2 text-xs text-muted-foreground font-medium">
            {{ $t('infra.fileManager.modals.upload.formats') }}
          </p>
        </div>

        <!-- Selected Files -->
        <div
          v-if="selectedFiles.length > 0"
          class="mt-6 space-y-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar"
        >
          <h4 class="text-xs font-semibold text-foreground/70 mb-3 flex items-center justify-between">
            <span>{{ $t('infra.fileManager.modals.upload.selectedFiles') }}</span>
            <span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded-md font-bold">{{ selectedFiles.length }}</span>
          </h4>
          <div
            v-for="(file, index) in selectedFiles"
            :key="index"
            class="group flex items-center justify-between p-2.5 bg-muted/30 hover:bg-muted/50 rounded-xl border border-border/40 transition-sm"
          >
            <div class="flex items-center flex-1 min-w-0 mr-4">
              <div class="h-10 w-10 rounded-lg overflow-hidden bg-background border border-border/40 flex-shrink-0 mr-3 flex items-center justify-center">
                <img
                  v-if="isImage(file)"
                  :src="getPreview(file)"
                  class="h-full w-full object-cover"
                >
                <component
                  :is="getFileIcon(file)"
                  v-else
                  class="h-5 w-5 text-muted-foreground"
                />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-foreground truncate">
                  {{ file.name }}
                </p>
                <p class="text-[10px] text-muted-foreground font-medium">
                  {{ formatFileSize(file.size) }}
                </p>
              </div>
            </div>
            <Button
              variant="ghost"
              size="icon"
              class="text-muted-foreground hover:text-destructive h-8 w-8 opacity-0 group-hover:opacity-100 transition-opacity"
              @click="removeFile(index)"
            >
              <Trash2
                class="w-4 h-4"
                stroke-width="1.5"
              />
            </Button>
          </div>
        </div>

        <!-- Upload Progress -->
        <div
          v-if="uploading"
          class="mt-6 bg-primary/5 p-4 rounded-xl border border-primary/10 animate-in fade-in zoom-in-95"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-primary uppercase tracking-widest">{{ $t('infra.fileManager.modals.upload.uploading') }}</span>
            <span class="text-xs font-bold text-primary">{{ uploadProgress }}%</span>
          </div>
          <div class="w-full bg-primary/10 rounded-full h-1.5 overflow-hidden">
            <div
              class="bg-primary h-full transition-[width] duration-300 ease-out shadow-[0_0_8px_rgba(var(--primary),0.5)]"
              :style="{ width: uploadProgress + '%' }"
            />
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          class="rounded-xl h-10 px-5 border-border/60 hover:bg-accent/10 text-foreground font-bold transition-colors"
          size="sm"
          @click="$emit('close')"
        >
          {{ $t('infra.fileManager.modals.upload.cancel') }}
        </Button>
        <Button
          :disabled="uploading || !isValid"
          class="rounded-xl h-10 px-5 bg-primary hover:bg-primary/90 text-primary-foreground font-bold shadow-lg shadow-primary/20 transition-[background-color,transform] active:scale-[0.98]"
          @click="handleUpload"
        >
          <Loader2
            v-if="uploading"
            class="mr-2 h-4 w-4 animate-spin"
          />
          {{ uploading ? $t('infra.fileManager.modals.upload.uploading') : $t('infra.fileManager.modals.upload.uploadAction', { count: selectedFiles.length }) }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed } from 'vue';
import api from '@/engine/api/client';
import {
    Button,
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter
} from '@/shared/components/ui';
import {
  CloudUpload,
  FileArchive,
  FileAudio,
  FileIcon,
  FileText,
  FileVideo,
  Loader2,
  Trash2,
} from 'lucide-vue-next';
import { useToast } from '@/shared/composables/useToast';

const toast = useToast();

const props = withDefaults(defineProps<{
    path?: string;
}>(), {
    path: '/'
});

const emit = defineEmits<{
    'close': [];
    'uploaded': [];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFiles = ref<File[]>([]);
const uploading = ref(false);
const uploadProgress = ref(0);
const isDragging = ref(false);
const previews = ref<Map<File, string>>(new Map());

const isImage = (file: File) => file.type.startsWith('image/');

const getFileIcon = (file: File) => {
    const type = file.type;
    if (type.startsWith('video/')) return FileVideo;
    if (type.startsWith('audio/')) return FileAudio;
    if (type.includes('pdf') || type.includes('word') || type.includes('text')) return FileText;
    if (type.includes('zip') || type.includes('rar') || type.includes('7z')) return FileArchive;
    return FileIcon;
};

const getPreview = (file: File) => {
    if (previews.value.has(file)) return previews.value.get(file);
    const url = URL.createObjectURL(file);
    previews.value.set(file, url);
    return url;
};

const cleanupPreviews = () => {
    previews.value.forEach(url => URL.revokeObjectURL(url));
    previews.value.clear();
};

const isValid = computed(() => {
    return selectedFiles.value.length > 0;
});

const triggerFileSelect = () => {
    fileInput.value?.click();
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        addFiles(Array.from(target.files));
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files) {
        addFiles(Array.from(event.dataTransfer.files));
    }
};

const addFiles = (files: File[]) => {
    files.forEach(file => {
        if (!selectedFiles.value.find(f => f.name === file.name && f.size === file.size)) {
            selectedFiles.value.push(file);
        }
    });
};

const removeFile = (index: number) => {
    const file = selectedFiles.value[index];
    if (file && previews.value.has(file)) {
        const preview = previews.value.get(file);
        if (preview) URL.revokeObjectURL(preview);
        previews.value.delete(file);
    }
    selectedFiles.value.splice(index, 1);
};

const handleUpload = async () => {
    if (selectedFiles.value.length === 0) return;

    uploading.value = true;
    uploadProgress.value = 0;
    try {
        const formData = new FormData();
        selectedFiles.value.forEach(file => {
            formData.append('files[]', file);
        });
        formData.append('path', props.path);

        await api.post('/manage/infra/file-manager/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                }
            },
        });
        
        toast.success.create('Files');
        emit('uploaded');
        emit('close');
        cleanupPreviews();
    } catch (error: unknown) {
        toast.error.fromResponse(error as import('axios').AxiosError);
        logger.error('Failed to upload files:', error);
    } finally {
        uploading.value = false;
    }
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};
</script>
