<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent
class="console-dialog-xl"
      @dragover.prevent
      @drop.prevent
    >
      <DialogHeader>
        <DialogTitle>{{ $t('media.modals.upload.title') }}</DialogTitle>
        <DialogDescription>
          Drag and drop files here or click to select files to upload.
        </DialogDescription>
      </DialogHeader>

      <div class="py-4">
        <!-- Drag & Drop Area -->
        <div
          :class="[
            'border-2 border-dashed rounded-xl p-12 text-center transition-colors cursor-pointer group/upload',
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
            class="mx-auto h-12 w-12 text-muted-foreground opacity-50 mb-4"
            stroke-width="1.5"
          />
          <div class="text-sm text-muted-foreground">
            <span class="text-primary font-medium group-hover/upload:underline">
              {{ $t('media.modals.upload.clickToUpload') }}
            </span>
            {{ $t('media.modals.upload.dragAndDrop') }}
          </div>
          <p class="mt-2 text-xs text-muted-foreground">
            {{ $t('media.modals.upload.formats') }}
          </p>
        </div>

        <!-- Selected Files -->
        <div
          v-if="selectedFiles.length > 0"
          class="mt-6 space-y-2 max-h-60 overflow-y-auto pr-2 custom-scrollbar"
        >
          <h4 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-3">
            {{ $t('media.modals.upload.selectedFiles') }}
          </h4>
          <div
            v-for="(file, index) in selectedFiles"
            :key="index"
            class="group flex items-center justify-between p-2 bg-muted/30 hover:bg-muted/50 rounded-xl border border-border/40 transition-colors"
          >
            <div class="flex items-center flex-1 min-w-0 mr-4">
              <div class="h-10 w-10 rounded-lg overflow-hidden bg-background border border-border/40 flex-shrink-0 mr-3">
                <img
                  v-if="isImage(file)"
                  :src="getPreview(file)"
                  class="h-full w-full object-cover"
                >
                <div
                  v-else
                  class="h-full w-full flex items-center justify-center text-muted-foreground"
                >
                  <FileIcon class="h-5 w-5" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-foreground truncate">
                  {{ file.name }}
                </p>
                <p class="text-[10px] text-muted-foreground uppercase">
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
          class="mt-6"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-foreground">{{ $t('media.modals.upload.uploading') }}</span>
            <span class="text-sm text-muted-foreground">{{ uploadProgress }}%</span>
          </div>
          <div class="w-full bg-secondary rounded-full h-2 overflow-hidden">
            <div
              class="bg-primary h-full transition-[width] duration-300 ease-out"
              :style="{ width: uploadProgress + '%' }"
            />
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          size="sm"
          class="h-10"
          @click="$emit('close')"
        >
          {{ $t('media.actions.cancel') }}
        </Button>
        <Button
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          :disabled="uploading || !isValid"
          @click="handleUpload"
        >
          <Loader2
            v-if="uploading"
            data-icon="inline-start"
            class="size-4 shrink-0 animate-spin"
          />
          {{ uploading ? $t('media.modals.upload.uploading') : $t('media.modals.upload.uploadAction', { count: selectedFiles.length }) }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import {
  CloudUpload,
  FileIcon,
  Loader2,
  Trash2,
} from 'lucide-vue-next';
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

const toast = useToast();

const props = defineProps<{
    folderId?: string | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'uploaded'): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFiles = ref<File[]>([]);
const uploading = ref(false);
const uploadProgress = ref(0);
const isDragging = ref(false);
const previews = ref<Map<File, string>>(new Map());

const isImage = (file: File) => file.type.startsWith('image/');

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
        const files = Array.from(target.files);
        addFiles(files);
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files) {
        const files = Array.from(event.dataTransfer.files);
        addFiles(files);
    }
};

const addFiles = (files: File[]) => {
    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            toast.error.fileTooLarge();
            return;
        }
        if (!selectedFiles.value.find(f => f.name === file.name && f.size === file.size)) {
            selectedFiles.value.push(file);
        }
    });
};

const removeFile = (index: number) => {
    const file = selectedFiles.value[index];
    if (!file) return;
    if (previews.value.has(file)) {
        URL.revokeObjectURL(previews.value.get(file)!);
        previews.value.delete(file);
    }
    selectedFiles.value.splice(index, 1);
};

const handleUpload = async () => {
    if (selectedFiles.value.length === 0) return;

    uploading.value = true;
    uploadProgress.value = 0;

    try {
        for (let i = 0; i < selectedFiles.value.length; i++) {
            const file = selectedFiles.value[i];
            if (!file) continue;
            const formData = new FormData();
            formData.append('file', file);
            if (props.folderId) {
                formData.append('folder_id', String(props.folderId));
            }

            await api.post('/manage/media/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    if (progressEvent.total) {
                        const fileProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        const totalProgress = Math.round(((i * 100) + fileProgress) / selectedFiles.value.length);
                        uploadProgress.value = totalProgress;
                    }
                },
            });
        }

        emit('uploaded');
        cleanupPreviews();
        selectedFiles.value = [];
        uploadProgress.value = 0;
    } catch (error: unknown) {
        logger.error('Upload error:', error);
        toast.error.fromResponse(error);
    } finally {
        uploading.value = false;
    }
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

