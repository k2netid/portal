<template>
  <div class="media-upload">
    <div
      v-if="!preview"
      class="border-2 border-dashed border-input rounded-lg p-6 text-center"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="effectiveConstraints.allowedExtensions.map((ext: string) => '.' + ext).join(',')"
        class="hidden"
        @change="handleFileSelect"
      >
      <button
        type="button"
        class="bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/80"
        @click="triggerFileSelect"
      >
        {{ $t('media.modals.upload.chooseFile') }}
      </button>
      <p class="mt-2 text-sm text-muted-foreground">
        {{ effectiveConstraints.allowedExtensions.join(', ').toUpperCase() }} 
        {{ $t('publishing.content.form.maxSizeHint', { size: (effectiveConstraints.maxSize / 1024).toFixed(0), extensions: '' }).replace('| ', '') }}
      </p>
      <p
        v-if="effectiveConstraints.minWidth || effectiveConstraints.minHeight"
        class="text-[10px] text-muted-foreground/60 italic"
      >
        {{ $t('publishing.content.form.minHint', { dimensions: (effectiveConstraints.minWidth || '?') + 'x' + (effectiveConstraints.minHeight || '?') + 'px' }) }}
      </p>
    </div>

    <div
      v-else
      class="relative"
    >
      <video 
        v-if="isPreviewVideo" 
        :src="preview" 
        class="w-full h-64 object-cover rounded-lg" 
        controls
      />
      <img
        v-else
        :src="preview"
        alt="Preview"
        class="w-full h-64 object-cover rounded-lg"
      >
            
      <div class="mt-4 flex space-x-2">
        <button
          type="button"
          :disabled="uploading"
          class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
          @click="uploadFile"
        >
          {{ uploading ? $t('media.modals.upload.uploading') : $t('media.modals.upload.upload') }}
        </button>
        <button
          type="button"
          class="bg-muted text-white px-4 py-2 rounded-md hover:bg-muted"
          @click="clearPreview"
        >
          {{ $t('media.actions.cancel') }}
        </button>
      </div>
      <div
        v-if="uploadedMedia"
        class="mt-4 p-4 bg-green-500/20 rounded-lg"
      >
        <p class="text-sm text-green-800 dark:text-green-400">
          {{ $t('media.modals.upload.uploadedSuccess') }}
        </p>
        <p class="text-xs text-green-600 dark:text-green-500 mt-1">
          {{ uploadedMedia.url }}
        </p>
      </div>
    </div>
        
    <div
      v-if="error"
      class="mt-3 p-2 bg-red-500/10 border border-red-500/20 rounded-md"
    >
      <p class="text-xs text-red-500 font-medium">
        {{ error }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { storeToRefs } from 'pinia';
import api from '@/engine/api/client';
import { useSystemStore } from '@/modules/Core/System/stores/system';
import { useToast } from '@/shared/composables/useToast';
import type { MediaConstraints, Media } from '@/modules/Content/Media/types/media';

const props = defineProps<{
    folderId?: string | null;
    constraints?: Partial<MediaConstraints>;
    path?: string;
    module?: string;
}>();

const emit = defineEmits<{
    (e: 'uploaded', media: Media): void;
}>();

const toast = useToast();
const { t } = useI18n();
const systemStore = useSystemStore();
const { settings } = storeToRefs(systemStore);

// Define reactive settings reference using storeToRefs
const globalMaxSize = computed(() => {
    // max_upload_size is stored in KB in settings
    const size = settings.value.max_upload_size;
    return size ? parseInt(String(size)) : 10240; // Default 10MB in KB
});

const effectiveConstraints = computed(() => {
    // Get all possible allowed extensions from settings
    const settingImages = settings.value.allowed_image_types ? String(settings.value.allowed_image_types).split(',').map(s => s.trim()) : [];
    const settingFiles = settings.value.allowed_file_types ? String(settings.value.allowed_file_types).split(',').map(s => s.trim()) : [];
    const settingVideos = settings.value.allowed_video_types ? String(settings.value.allowed_video_types).split(',').map(s => s.trim()) : [];
    const settingAudio = settings.value.allowed_audio_types ? String(settings.value.allowed_audio_types).split(',').map(s => s.trim()) : [];
    
    const allSystemAllowed = [
        ...settingImages,
        ...settingFiles,
        ...settingVideos,
        ...settingAudio
    ];

    const defaultExtensions = allSystemAllowed.length > 0 
        ? allSystemAllowed 
        : ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
    const allowedExtensions = props.constraints?.allowedExtensions || defaultExtensions;
    
    // System limit is the hard cap
    const systemLimitKB = globalMaxSize.value;
    
    // Feature limit (if provided) is capped by system limit
    let maxSizeKB = props.constraints?.maxSize ? parseInt(props.constraints.maxSize.toString()) : systemLimitKB;
    if (maxSizeKB > systemLimitKB) {
        maxSizeKB = systemLimitKB;
    }

    return {
        ...props.constraints,
        allowedExtensions,
        maxSize: maxSizeKB
    };
});

const fileInput = ref<HTMLInputElement | null>(null);
const preview = ref<string | null>(null);
const selectedFile = ref<File | null>(null);
const uploading = ref(false);
const uploadedMedia = ref<Media | null>(null);
const error = ref<string | null>(null);

onMounted(async () => {
    // Ensure media settings are loaded for the limits
    await systemStore.fetchSettingsGroup('media');
});

const isPreviewVideo = computed(() => {
    if (!selectedFile.value) return false;
    return selectedFile.value.type.startsWith('video/');
});

const triggerFileSelect = () => {
    fileInput.value?.click();
};

const handleFileSelect = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    error.value = null;
    const extension = file.name.split('.').pop()?.toLowerCase() || '';
    
    // Check extension
    if (effectiveConstraints.value.allowedExtensions && !effectiveConstraints.value.allowedExtensions.includes(extension)) {
        const msg = `File type .${extension} is not allowed. Allowed: ${effectiveConstraints.value.allowedExtensions.join(', ')}`;
        error.value = msg;
        toast.error.validation(msg);
        return;
    }

    // Check size
    const maxSizeInBytes = (effectiveConstraints.value.maxSize || (Number(settings.value.max_upload_size) || 10240)) * 1024;
    if (file.size > maxSizeInBytes) {
        const msg = `File size exceeds limit of ${formatSize(maxSizeInBytes)}`;
        error.value = msg;
        toast.error.validation(msg);
        return;
    }

    // Check dimensions for images
    if (file.type.startsWith('image/') && !file.type.includes('svg')) {
        try {
            const dimensions = await getImageDimensions(file);
            const { minWidth, minHeight, maxWidth, maxHeight } = effectiveConstraints.value;

            if (minWidth && dimensions.width < minWidth) {
                error.value = `Image width must be at least ${minWidth}px (current: ${dimensions.width}px)`;
                toast.error.validation(error.value as string);
                return;
            }
            if (minHeight && dimensions.height < minHeight) {
                error.value = `Image height must be at least ${minHeight}px (current: ${dimensions.height}px)`;
                toast.error.validation(error.value as string);
                return;
            }
            if (maxWidth && dimensions.width > maxWidth) {
                error.value = `Image width exceeds limit of ${maxWidth}px (current: ${dimensions.width}px)`;
                toast.error.validation(error.value as string);
                return;
            }
            if (maxHeight && dimensions.height > maxHeight) {
                error.value = `Image height exceeds limit of ${maxHeight}px (current: ${dimensions.height}px)`;
                toast.error.validation(error.value as string);
                return;
            }
        } catch (e) {
            error.value = t('media.modals.upload.errorLoadingImage');
            if (error.value) toast.error.validation(error.value);
            logger.error('Failed to get image dimensions:', e);
            return;
        }
    }

    selectedFile.value = file;

    // Create preview using URL.createObjectURL instead of FileReader for performance
    if (preview.value && preview.value.startsWith('blob:')) {
        URL.revokeObjectURL(preview.value);
    }
    preview.value = URL.createObjectURL(file);
};

const getImageDimensions = (file: File): Promise<{ width: number; height: number }> => {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve({ width: img.width, height: img.height });
        };
        img.onerror = reject;
        img.src = url;
    });
};

const uploadFile = async () => {
    if (!selectedFile.value) return;

    uploading.value = true;
    error.value = null;
    const formData = new FormData();
    formData.append('file', selectedFile.value);
    
    if (props.folderId) {
        formData.append('folder_id', props.folderId.toString());
    }

    if (props.path) {
        formData.append('path', props.path);
    }

    if (props.module) {
        formData.append('module', props.module);
    }

    // Pass constraints to backend for double-checked validation
    if (effectiveConstraints.value.minWidth) formData.append('min_width', effectiveConstraints.value.minWidth.toString());
    if (effectiveConstraints.value.minHeight) formData.append('min_height', effectiveConstraints.value.minHeight.toString());
    if (effectiveConstraints.value.maxWidth) formData.append('max_width', effectiveConstraints.value.maxWidth.toString());
    if (effectiveConstraints.value.maxHeight) formData.append('max_height', effectiveConstraints.value.maxHeight.toString());

    try {
        const response = await api.post('/manage/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        const responseData = response.data;
        const media = responseData.data?.media || responseData.data || responseData;
        uploadedMedia.value = media;
        emit('uploaded', media);
        
        // Clear after 3 seconds
        setTimeout(() => {
            clearPreview();
        }, 3000);
    } catch (err: unknown) {
        logger.error('Upload error:', err);
        const errorResponse = (err as { response?: { data?: Record<string, unknown> } })?.response?.data;
        const errors = errorResponse?.errors as Record<string, string[]> | undefined;
        const backendError = errors?.file?.[0] || (typeof errorResponse?.message === 'string' ? errorResponse.message : '') || 'Failed to upload file';
        error.value = backendError;
    } finally {
        uploading.value = false;
    }
};

const clearPreview = () => {
    if (preview.value && preview.value.startsWith('blob:')) {
        URL.revokeObjectURL(preview.value);
    }
    preview.value = null;
    selectedFile.value = null;
    uploadedMedia.value = null;
    error.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const formatSize = (bytes: number) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

