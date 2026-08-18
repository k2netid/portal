<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="console-dialog-md sm:max-w-md bg-card border border-border/80 rounded-xl">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Upload class="w-5 h-5 text-indigo-500" />
          {{ t('system.appStore.uploadTitle') }}
        </DialogTitle>
      </DialogHeader>

      <div class="bg-indigo-500/10 border border-indigo-500/20 p-3 rounded-lg flex gap-3 text-xs text-indigo-400 mt-2">
        <ShieldCheck class="w-6 h-6 shrink-0 mt-0.5" />
        <div>
          <span class="font-bold block">{{ t('system.appStore.sandboxNoticeTitle') }}</span>
          {{ t('system.appStore.sandboxNoticeDesc') }}
        </div>
      </div>

      <div class="mt-6 space-y-4">
        <div
          class="border-2 border-dashed border-border/80 rounded-xl p-8 flex flex-col items-center justify-center cursor-pointer hover:border-indigo-500/50 transition-colors"
          @dragover.prevent
          @drop.prevent="handleFileDrop"
          @click="triggerFileInput"
        >
          <input
            ref="fileInput"
            type="file"
            accept=".zip"
            class="hidden"
            @change="handleFileSelect"
          />
          <Upload class="w-10 h-10 text-muted-foreground/60 mb-2 animate-bounce" />
          <span class="text-sm font-semibold text-foreground text-center">
            {{ selectedFile ? selectedFile.name : t('system.appStore.dragDropLabel') }}
          </span>
          <span class="text-xs text-muted-foreground mt-1">{{ t('system.appStore.manifestNotice') }}</span>
        </div>

        <div
          v-if="uploadError"
          class="bg-rose-500/10 border border-rose-500/20 p-3 rounded-lg flex gap-2 text-xs text-rose-400"
        >
          <AlertTriangle class="w-5 h-5 shrink-0" />
          <span>{{ uploadError }}</span>
        </div>
      </div>

      <DialogFooter class="mt-6 flex items-center justify-end gap-2">
        <Button
          variant="secondary"
          @click="$emit('update:open', false)"
        >
          {{ t('system.appStore.cancel') }}
        </Button>
        <Button
          :disabled="!selectedFile || uploading"
          class="bg-indigo-600 hover:bg-indigo-700 text-white border-0"
          @click="submitUpload"
        >
          <span v-if="uploading">{{ t('system.appStore.installing') }}</span>
          <span v-else>{{ t('system.appStore.installBtn') }}</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, Button } from '@/shared/components/ui';
import {
  AlertTriangle,
  ShieldCheck,
  Upload,
} from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps<{
  open: boolean;
  uploading: boolean;
  uploadError: string;
}>();

const emit = defineEmits<{
  (e: 'update:open', val: boolean): void;
  (e: 'upload', file: File): void;
  (e: 'clear-error'): void;
}>();

const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

watch(() => props.open, (newVal) => {
  if (!newVal) {
    selectedFile.value = null;
    emit('clear-error');
  }
});

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    const file = target.files[0];
    if (file) {
      selectedFile.value = file;
      emit('clear-error');
    }
  }
};

const handleFileDrop = (event: DragEvent) => {
  if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
    const file = event.dataTransfer.files[0];
    if (file && file.name.endsWith('.zip')) {
      selectedFile.value = file;
      emit('clear-error');
    }
  }
};

const submitUpload = () => {
  if (selectedFile.value) {
    emit('upload', selectedFile.value);
  }
};
</script>
