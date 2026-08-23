<template>
  <div
    class="fixed inset-0 z-50 overflow-y-auto bg-background/80 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <div class="flex items-center justify-center min-h-screen px-4">
      <div class="bg-card border border-border/40 shadow-none rounded-xl max-w-5xl w-full max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-3 border-b border-border/40 shrink-0">
          <h3 class="text-base font-semibold truncate">
            {{ media.name }}
          </h3>
          <Button
            variant="ghost"
            size="icon"
            class="h-10 w-10"
            :aria-label="$t('media.actions.close')"
            @click="$emit('close')"
          >
            <X
              class="size-4 shrink-0"
              stroke-width="1.5"
            />
          </Button>
        </div>

        <!-- Content -->
        <div class="px-5 py-4 overflow-y-auto flex-1">
          <!-- Grid Layout: Image Left, Details Right -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Left: Image Preview -->
            <div class="flex flex-col">
              <div v-if="media.mime_type?.startsWith('image/')">
                <!-- Display Mode Selector -->
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-medium text-muted-foreground">{{ $t('media.modals.view.preview') }}</span>
                  <div class="flex items-center gap-0.5 bg-muted p-0.5 rounded-xl">
                    <Button
                      v-for="mode in displayModes"
                      :key="mode.value"
                      variant="ghost"
                      size="sm"
                      :class="[
                        'h-6 px-2 text-xs',
                        displayMode === mode.value ? 'bg-background shadow-sm font-medium' : 'text-muted-foreground'
                      ]"
                      @click="displayMode = mode.value"
                    >
                      <component
                        :is="mode.icon"
                        class="w-3 h-3 mr-1"
                      />
                      {{ $t(`media.modals.view.${mode.value}`) }}
                    </Button>
                  </div>
                </div>

                <!-- Preview Container -->
                <div 
                  ref="previewContainer"
                  class="relative rounded-xl bg-secondary border border-border/40 h-72"
                  :class="displayMode === 'actual' ? 'overflow-auto' : 'overflow-hidden'"
                  :style="displayMode === 'actual' ? { cursor: isDragging ? 'grabbing' : 'grab' } : {}"
                  @mousedown="startDrag"
                  @mousemove="onDrag"
                  @mouseup="stopDrag"
                  @mouseleave="stopDrag"
                  @touchstart="startDrag"
                  @touchmove="onDrag"
                  @touchend="stopDrag"
                >
                  <img 
                    v-if="displayMode === 'contain'"
                    :src="media.url" 
                    :alt="media.alt || media.name" 
                    :style="{ width: '100%', height: '100%', objectFit: 'contain' }"
                  >
                  <img 
                    v-else-if="displayMode === 'stretch'"
                    :src="media.url" 
                    :alt="media.alt || media.name" 
                    :style="{ 
                      width: '100%', 
                      height: '100%', 
                      objectFit: 'cover',
                      objectPosition: `${fillPosition.x}% ${fillPosition.y}%`,
                      cursor: isDragging ? 'grabbing' : 'grab'
                    }"
                  >
                  <img 
                    v-else-if="displayMode === 'actual'"
                    :src="media.url" 
                    :alt="media.alt || media.name" 
                    class="select-none"
                    :style="{ maxWidth: 'none', maxHeight: 'none', minWidth: 'auto', minHeight: 'auto' }"
                    draggable="false"
                  >
                </div>

                <!-- Pan Hint -->
                <p
                  v-if="displayMode === 'actual' || displayMode === 'stretch'"
                  class="text-xs text-muted-foreground mt-1.5 text-center"
                >
                  <Move
                    class="w-3 h-3 inline-block mr-1"
                    stroke-width="1.5"
                  />
                  {{ $t('media.modals.view.dragToPan') }}
                </p>
              </div>
                            
              <!-- Video Preview -->
              <div v-else-if="media.mime_type?.startsWith('video/')">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-xs font-medium text-muted-foreground">{{ $t('media.modals.view.preview') }}</span>
                </div>
                <div class="relative rounded-xl bg-secondary border border-border/40 h-72 overflow-hidden">
                  <video 
                    :src="media.url" 
                    controls 
                    class="w-full h-full object-contain"
                    preload="metadata"
                  >
                    Your browser does not support the video tag.
                  </video>
                </div>
              </div>
                            
              <!-- Other File Types -->
              <div
                v-else
                class="bg-secondary border border-border/40 rounded-xl flex items-center justify-center h-72"
              >
                <FileText
                  class="w-16 h-16 text-muted-foreground opacity-50"
                  stroke-width="1.5"
                />
              </div>

              <!-- Quick Actions for Images -->
              <div
                v-if="media.mime_type?.startsWith('image/')"
                class="flex flex-wrap gap-2 mt-3"
              >
                <Button
                  size="sm"
                  variant="secondary"
                  class="bg-purple-600 text-white hover:bg-purple-700"
                  @click="showImageEditor = true"
                >
                  <Edit
                    class="w-3.5 h-3.5 mr-1.5"
                    stroke-width="1.5"
                  />
                  {{ $t('media.modals.view.edit') }}
                </Button>
                <Button
                  size="sm"
                  variant="outline"
                  @click="showResizeModal = true"
                >
                  <Scissors
                    class="w-3.5 h-3.5 mr-1.5"
                    stroke-width="1.5"
                  />
                  {{ $t('media.modals.view.resize') }}
                </Button>
                <Button
                  size="sm"
                  variant="outline"
                  :disabled="generatingThumbnail"
                  @click="generateThumbnail"
                >
                  <RefreshCw
                    :class="['w-3.5 h-3.5 mr-1.5', generatingThumbnail ? 'animate-spin' : '']"
                    stroke-width="1.5"
                  />
                  {{ generatingThumbnail ? $t('media.modals.view.generating') : $t('media.modals.view.thumbnail') }}
                </Button>
              </div>
            </div>

            <!-- Right: Details -->
            <div class="space-y-4">
              <!-- Media Details -->
              <div class="bg-muted/50 border border-border/40 rounded-xl p-3">
                <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">
                  {{ $t('media.modals.view.details') }}
                </h4>
                <dl class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <dt class="text-muted-foreground">
                      {{ $t('media.modals.view.name') }}
                    </dt>
                    <dd class="text-foreground font-medium text-right truncate max-w-[60%]">
                      {{ media.name }}
                    </dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-muted-foreground">
                      {{ $t('media.modals.view.type') }}
                    </dt>
                    <dd class="text-foreground">
                      {{ media.mime_type }}
                    </dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-muted-foreground">
                      {{ $t('media.modals.view.size') }}
                    </dt>
                    <dd class="text-foreground">
                      {{ formatFileSize(media.size) }}
                    </dd>
                  </div>
                  <div
                    v-if="media.folder"
                    class="flex justify-between"
                  >
                    <dt class="text-muted-foreground">
                      {{ $t('media.modals.view.folder') }}
                    </dt>
                    <dd class="text-foreground">
                      {{ media.folder.name }}
                    </dd>
                  </div>
                  <div
                    v-if="media.alt"
                    class="flex justify-between"
                  >
                    <dt class="text-muted-foreground">
                      {{ $t('media.modals.view.altText') }}
                    </dt>
                    <dd class="text-foreground text-right truncate max-w-[60%]">
                      {{ media.alt }}
                    </dd>
                  </div>
                </dl>
              </div>

              <!-- Tags -->
              <div
                v-if="media.tag_names && media.tag_names.length > 0"
                class="bg-muted/50 border border-border/40 rounded-xl p-3"
              >
                <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">
                  {{ $t('media.tags') }}
                </h4>
                <div class="flex flex-wrap gap-1.5">
                  <span 
                    v-for="tag in media.tag_names" 
                    :key="tag"
                    class="px-2 py-0.5 bg-background border border-border/40 rounded-md text-[10px] font-medium text-foreground"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>

              <!-- URL Copy -->
              <div class="bg-muted/50 border border-border/40 rounded-xl p-3">
                <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">
                  {{ $t('media.modals.view.url') }}
                </h4>
                <div class="flex items-center gap-2">
                  <input
                    :value="media.url"
                    readonly
                    class="flex-1 px-2 py-1.5 border border-border/40 bg-card text-foreground rounded-lg text-xs font-mono"
                  >
                  <Button
                    size="sm"
                    variant="outline"
                    @click="copyUrl"
                  >
                    <Copy
                      class="w-3.5 h-3.5"
                      stroke-width="1.5"
                    />
                  </Button>
                </div>
              </div>

              <!-- Usage -->
              <div class="bg-muted/50 border border-border/40 rounded-xl p-3">
                <h4 class="text-xs font-medium text-muted-foreground uppercase tracking-wider mb-2">
                  {{ $t('media.modals.view.usage') }}
                </h4>
                <div
                  v-if="loadingUsage"
                  class="text-xs text-muted-foreground"
                >
                  {{ $t('media.modals.view.loadingUsage') }}
                </div>
                <div
                  v-else-if="usageDetail && usageDetail.length > 0"
                  class="space-y-1.5 max-h-24 overflow-y-auto"
                >
                  <div
                    v-for="usage in usageDetail"
                    :key="usage.id"
                    class="text-xs text-foreground p-1.5 bg-background border border-border/40 rounded-lg"
                  >
                    <span class="font-medium">{{ usage.type }}</span>
                    <span
                      v-if="usage.title"
                      class="text-muted-foreground"
                    > · {{ usage.title }}</span>
                  </div>
                </div>
                <div
                  v-else
                  class="text-xs text-muted-foreground"
                >
                  {{ $t('media.modals.view.notUsed') }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end px-5 py-3 border-t border-border/40 shrink-0">
          <Button
            variant="outline"
            @click="$emit('close')"
          >
            {{ $t('media.modals.view.close') }}
          </Button>
        </div>
      </div>
    </div>

    <!-- Resize Modal -->
    <ResizeMediaModal
      v-if="showResizeModal"
      :media="media"
      @close="showResizeModal = false"
      @resized="handleResized"
    />

    <!-- Image Editor Modal -->
    <ImageEditor
      v-if="showImageEditor"
      :media="media"
      @close="showImageEditor = false"
      @updated="handleImageEdited"
    />
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, markRaw } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from '@/shared/composables/useToast';
import {
  Copy,
  Edit,
  FileText,
  Maximize,
  Move,
  RefreshCw,
  Scissors,
  Square,
  X,
} from 'lucide-vue-next';
import { MediaService } from '@/modules/Media/services/mediaService';
import { Button } from '@/shared/components/ui';
import ResizeMediaModal from './ResizeMediaModal.vue';
import ImageEditor from './ImageEditor.vue';
import type { Media } from '@/modules/Media/types/media';

interface UsageDetail {
    id: string;
    type: string;
    title?: string;
}

interface DragStart {
    x: number;
    y: number;
    scrollLeft: number;
    scrollTop: number;
    fillX: number;
    fillY: number;
}

const props = defineProps<{
    media: Media;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'updated'): void;
}>();

const { t } = useI18n();
const toast = useToast();

const loadingUsage = ref(false);
const usageDetail = ref<UsageDetail[]>([]);
const generatingThumbnail = ref(false);
const showResizeModal = ref(false);
const showImageEditor = ref(false);

// Display mode state
const displayMode = ref<'contain' | 'stretch' | 'actual'>('stretch');
const displayModes = [
    { value: 'contain' as const, label: 'Fit', icon: markRaw(Square) },
    { value: 'stretch' as const, label: 'Fill', icon: markRaw(Maximize) },
    { value: 'actual' as const, label: '1:1', icon: markRaw(Move) },
];

// Drag state
const previewContainer = ref<HTMLElement | null>(null);
const isDragging = ref(false);
const dragStart = ref<DragStart>({ x: 0, y: 0, scrollLeft: 0, scrollTop: 0, fillX: 50, fillY: 50 });
const fillPosition = ref({ x: 50, y: 50 });

const startDrag = (e: MouseEvent | TouchEvent) => {
    if (displayMode.value !== 'actual' && displayMode.value !== 'stretch') return;
    isDragging.value = true;
    
    const touch = 'touches' in e ? e.touches[0] : null;
    const clientX = touch ? touch.clientX : (e as MouseEvent).clientX;
    const clientY = touch ? touch.clientY : (e as MouseEvent).clientY;
    
    dragStart.value = {
        x: clientX,
        y: clientY,
        scrollLeft: previewContainer.value?.scrollLeft || 0,
        scrollTop: previewContainer.value?.scrollTop || 0,
        fillX: fillPosition.value.x,
        fillY: fillPosition.value.y,
    };
};

const onDrag = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value) return;
    if (displayMode.value !== 'actual' && displayMode.value !== 'stretch') return;
    
    const touch = 'touches' in e ? e.touches[0] : null;
    const clientX = touch ? touch.clientX : (e as MouseEvent).clientX;
    const clientY = touch ? touch.clientY : (e as MouseEvent).clientY;
    
    const dx = clientX - dragStart.value.x;
    const dy = clientY - dragStart.value.y;

    if (displayMode.value === 'actual' && previewContainer.value) {
        previewContainer.value.scrollLeft = dragStart.value.scrollLeft - dx;
        previewContainer.value.scrollTop = dragStart.value.scrollTop - dy;
    } else if (displayMode.value === 'stretch' && previewContainer.value) {
        const containerWidth = previewContainer.value.clientWidth;
        const containerHeight = previewContainer.value.clientHeight;
        
        const deltaXPercent = (dx / containerWidth) * 100;
        const deltaYPercent = (dy / containerHeight) * 100;

        fillPosition.value = {
            x: Math.max(0, Math.min(100, dragStart.value.fillX - deltaXPercent)),
            y: Math.max(0, Math.min(100, dragStart.value.fillY - deltaYPercent)),
        };
    }
};

const stopDrag = () => {
    isDragging.value = false;
};

const fetchUsageDetail = async () => {
    loadingUsage.value = true;
    try {
        const response = await MediaService.usage(String(props.media.id));
        const data = response.data || [];
        usageDetail.value = data;
    } catch (error) {
        logger.error('Failed to fetch usage detail:', error);
        usageDetail.value = [];
    } finally {
        loadingUsage.value = false;
    }
};

const generateThumbnail = async () => {
    generatingThumbnail.value = true;
    try {
        await MediaService.thumbnail(String(props.media.id));
        // We need a proper success toast for thumbnail
        toast.success.action(t('media.modals.view.thumbnailGenerated'));
        emit('updated');
    } catch (error: unknown) {
        logger.error('Failed to generate thumbnail:', error);
        toast.error.fromResponse(error);
    } finally {
        generatingThumbnail.value = false;
    }
};

const handleResized = () => {
    emit('updated');
    showResizeModal.value = false;
};

const handleImageEdited = () => {
    emit('updated');
    showImageEditor.value = false;
};

const copyUrl = () => {
    if (props.media.url) {
        navigator.clipboard.writeText(props.media.url);
        toast.success.urlCopied();
    }
};

const formatFileSize = (bytes: number) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(() => {
    fetchUsageDetail();
});
</script>
