<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-[95vw] w-full max-h-[95vh] h-full flex flex-col p-0 overflow-hidden bg-black/95 border-none shadow-2xl">
      <!-- Header Overlay -->
      <DialogHeader class="absolute top-0 left-0 right-0 z-10 flex flex-row items-center justify-between p-4 bg-gradient-to-b from-black/80 to-transparent">
        <DialogTitle class="text-white truncate pr-8 flex items-center gap-2 text-sm sm:text-base font-normal">
          <FileIcon class="w-4 h-4 text-white/70" />
          {{ fileName || 'File Preview' }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          {{ fileName || 'File Preview' }}
        </DialogDescription>
        <div class="flex items-center gap-2">
          <Button variant="ghost" size="icon" class="text-white hover:bg-white/20 rounded-full" @click="downloadFile" title="Download">
            <DownloadIcon class="w-5 h-5" />
          </Button>
          <Button variant="ghost" size="icon" class="text-white hover:bg-white/20 rounded-full" @click="$emit('update:open', false)" title="Close">
            <XIcon class="w-5 h-5" />
          </Button>
        </div>
      </DialogHeader>

      <!-- Content Area -->
      <div class="flex-1 flex items-center justify-center overflow-auto p-4 sm:p-8 mt-14 mb-4">
        <Spinner v-if="loading" class="text-white w-8 h-8" />
        
        <template v-else>
          <!-- Image Viewer -->
          <div v-if="isImage" class="relative w-full h-full flex items-center justify-center">
            <img 
              :src="inlineUrl" 
              :alt="fileName" 
              class="max-w-full max-h-full object-contain drop-shadow-lg"
              @error="handleError"
            />
          </div>

          <!-- PDF Viewer -->
          <iframe 
            v-else-if="isPdf" 
            :src="inlineUrl" 
            class="w-full h-full bg-white rounded-md shadow-lg"
            @load="loading = false"
            @error="handleError"
          ></iframe>

          <!-- Fallback Viewer (Unsupported Type or Error) -->
          <div v-else class="text-center text-white/80 flex flex-col items-center gap-4 bg-white/5 p-8 rounded-2xl max-w-sm w-full backdrop-blur-sm border border-white/10">
            <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center">
              <FileWarningIcon class="w-8 h-8 text-white/70" />
            </div>
            <div>
              <h3 class="text-lg font-medium text-white mb-1">Preview Not Available</h3>
              <p class="text-sm text-white/60">This file type ({{ mimeType || 'unknown' }}) cannot be previewed in the browser.</p>
            </div>
            <Button variant="outline" class="w-full mt-4 bg-white/10 border-white/20 text-white hover:bg-white/20 hover:text-white" @click="downloadFile">
              <DownloadIcon class="w-4 h-4 mr-2" /> Download File
            </Button>
          </div>
        </template>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, Button, Spinner } from '@/shared/components/ui';
import { X as XIcon, Download as DownloadIcon, File as FileIcon, FileWarning as FileWarningIcon } from 'lucide-vue-next';

const props = defineProps<{
  open: boolean;
  url: string;
  fileName?: string;
  mimeType?: string;
}>();

defineEmits<{
  (e: 'update:open', value: boolean): void;
}>();

const loading = ref(false);
const error = ref(false);

const inlineUrl = computed(() => {
  if (!props.url) return '';
  if (props.url.startsWith('blob:')) return props.url;
  const separator = props.url.includes('?') ? '&' : '?';
  return `${props.url}${separator}inline=1`;
});

const isImage = computed(() => {
  if (error.value) return false;
  return (
    props.mimeType?.startsWith('image/') || 
    props.url.match(/\.(jpeg|jpg|gif|png|webp)$/i) != null ||
    props.fileName?.match(/\.(jpeg|jpg|gif|png|webp)$/i) != null
  );
});

const isPdf = computed(() => {
  if (error.value) return false;
  return (
    props.mimeType === 'application/pdf' || 
    props.url.match(/\.pdf$/i) != null ||
    props.fileName?.match(/\.pdf$/i) != null
  );
});

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    error.value = false;
    loading.value = !props.url; 
  } else {
    loading.value = false;
  }
});

watch(() => props.url, (newUrl) => {
  if (newUrl) {
    error.value = false;
    loading.value = false;
  } else {
    loading.value = true;
  }
});

const handleError = () => {
  error.value = true;
  loading.value = false;
};

const downloadFile = () => {
  // Trigger download by opening original URL (without inline=1 or with download logic)
  // Usually, opening the URL directly works, or creating an anchor tag
  const a = document.createElement('a');
  a.href = props.url;
  a.download = props.fileName || 'download';
  a.target = '_blank';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
};
</script>
