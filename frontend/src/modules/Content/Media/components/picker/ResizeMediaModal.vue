<template>
  <div
    class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="bg-card border border-border/40 shadow-none rounded-xl max-w-2xl w-full">
        <div class="flex items-center justify-between p-6 border-b border-border/40">
          <h3 class="text-lg font-semibold">
            {{ $t('media.modals.resize.title') }}
          </h3>
          <Button
            variant="ghost"
            size="icon"
            @click="$emit('close')"
          >
            <X
              class="w-5 h-5"
              stroke-width="1.5"
            />
          </Button>
        </div>

        <div class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-foreground mb-1">
                {{ $t('media.modals.resize.width') }}
              </label>
              <input
                v-model.number="width"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-border/40 bg-card text-foreground rounded-xl focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-foreground mb-1">
                {{ $t('media.modals.resize.height') }}
              </label>
              <input
                v-model.number="height"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-border/40 bg-card text-foreground rounded-xl focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
              >
            </div>
          </div>

          <div class="flex items-center">
            <input
              id="aspect-ratio"
              v-model="maintainAspectRatio"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-border/40 rounded-lg"
            >
            <label
              for="aspect-ratio"
              class="ml-2 block text-sm text-foreground"
            >
              {{ $t('media.modals.resize.maintainAspectRatio') }}
            </label>
          </div>

          <div
            v-if="media.mime_type?.startsWith('image/')"
            class="mt-4"
          >
            <label class="block text-sm font-medium text-foreground mb-2">{{ $t('media.modals.resize.preview') }}</label>
            <div class="border border-border/40 rounded-xl p-4 bg-muted/30">
              <img
                :src="media.url"
                :alt="media.name"
                class="max-w-full h-auto mx-auto"
                :style="previewStyle"
                @load="onImageLoad"
              >
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end space-x-3 p-6 border-t border-border/40">
          <Button
            variant="outline"
            @click="$emit('close')"
          >
            {{ $t('media.modals.resize.cancel') }}
          </Button>
          <Button
            :disabled="resizing || !isValid"
            @click="handleResize"
          >
            {{ resizing ? $t('media.modals.resize.resizing') : $t('media.modals.resize.resize') }}
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  X,
} from 'lucide-vue-next';
import { MediaService } from '@/modules/Content/Media/services/mediaService';
import { useToast } from '@/shared/composables/useToast';
import { Button } from '@/shared/components/ui';
import type { Media } from '@/modules/Content/Media/types/media';

const props = defineProps<{
    media: Media;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'resized'): void;
}>();

const { t } = useI18n();
const toast = useToast();

const width = ref<number | null>(null);
const height = ref<number | null>(null);
const maintainAspectRatio = ref(true);
const resizing = ref(false);
const originalDimensions = ref({ width: 0, height: 0 });

const originalAspectRatio = computed(() => {
    if (originalDimensions.value.width && originalDimensions.value.height) {
        return originalDimensions.value.width / originalDimensions.value.height;
    }
    return null;
});

const isValid = computed(() => {
    return !!width.value && !!height.value;
});

const onImageLoad = (e: Event) => {
    const img = e.target as HTMLImageElement;
    width.value = img.naturalWidth;
    height.value = img.naturalHeight;
    originalDimensions.value = {
        width: img.naturalWidth,
        height: img.naturalHeight
    };
};

const previewStyle = computed(() => {
    if (!width.value || !height.value) return {};
    return {
        aspectRatio: `${width.value} / ${height.value}`,
        maxHeight: '300px',
        maxWidth: '100%',
        objectFit: 'contain' as const,
    };
});

const isUpdating = ref(false);

watch(width, (newWidth) => {
    if (isUpdating.value || !maintainAspectRatio.value || !originalAspectRatio.value || !newWidth) return;
    
    isUpdating.value = true;
    height.value = Math.round(newWidth / originalAspectRatio.value);
    isUpdating.value = false;
});

watch(height, (newHeight) => {
    if (isUpdating.value || !maintainAspectRatio.value || !originalAspectRatio.value || !newHeight) return;
    
    isUpdating.value = true;
    width.value = Math.round(newHeight * originalAspectRatio.value);
    isUpdating.value = false;
});

const handleResize = async () => {
    if (!width.value || !height.value) return;

    resizing.value = true;
    try {
        await MediaService.resize(String(props.media.id), {
            width: width.value,
            height: height.value,
            maintain_aspect_ratio: maintainAspectRatio.value,
        });
        toast.success.action(t('media.modals.resize.success'));
        emit('resized');
    } catch (error: unknown) {
        logger.error('Failed to resize media:', error);
        toast.error.fromResponse(error);
    } finally {
        resizing.value = false;
    }
};
</script>

