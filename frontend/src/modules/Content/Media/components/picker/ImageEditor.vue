<template>
  <div
    class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-background/95 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <!-- Main Container -->
    <div
      class="relative flex flex-col w-full h-full max-w-5xl max-h-[90vh] bg-card md:rounded-xl overflow-hidden shadow-2xl border border-border"
      tabindex="0"
      autofocus
      @keydown.enter="handleEnterKey"
    >
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-border bg-card/50 backdrop-blur-md z-10">
        <div class="text-sm font-medium text-foreground/80">
          {{ t('media.modals.editor.title') }}
        </div>
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2 mr-2">
            <input
              id="saveAsNew"
              v-model="saveAsNew"
              type="checkbox"
              class="rounded border-border bg-muted text-primary focus:ring-primary/50"
            >
            <label
              for="saveAsNew"
              class="text-xs text-muted-foreground cursor-pointer select-none"
            >{{ t('media.modals.editor.saveAsNew') }}</label>
          </div>
                    
          <div
            v-if="saveAsNew"
            class=""
          >
            <input 
              v-model="customFilename" 
              type="text" 
              class="bg-muted border border-border rounded px-2 py-1 text-xs text-foreground w-32 focus:outline-none focus:border-primary/40 focus:bg-accent placeholder-muted-foreground/40"
              :placeholder="t('media.modals.editor.fileNamePlaceholder')"
            >
          </div>

          <Button
            variant="ghost"
            size="sm"
            class="text-muted-foreground hover:text-foreground"
            @click="$emit('close')"
          >
            <X
              class="w-4 h-4 mr-2"
              stroke-width="1.5"
            />
            {{ t('common.actions.cancel') }}
          </Button>
          <Button
            size="sm"
            :disabled="saving || !hasChanges"
            class="bg-primary hover:bg-primary/90 text-primary-foreground min-w-[100px]"
            @click="saveImage"
          >
            <Save
              v-if="!saving"
              class="w-4 h-4 mr-2"
              stroke-width="1.5"
            />
            <span
              v-else
              class="w-4 h-4 mr-2 animate-spin rounded-full border-2 border-current border-t-transparent"
            />
            {{ t('common.actions.save') }}
          </Button>
        </div>
      </div>

      <!-- Canvas Area -->
      <div class="flex-1 relative flex items-center justify-center bg-black/95 overflow-hidden p-8 user-select-none">
        <div
          v-if="!imageLoaded"
          class="absolute inset-0 flex items-center justify-center text-muted-foreground/40"
        >
          <span class="animate-pulse">{{ t('media.modals.editor.loadingImage') }}</span>
        </div>
                
        <!-- Main Image Display -->
        <div class="relative max-w-full max-h-full">
          <img 
            ref="imageElement"
            :src="currentImageSrc" 
            class="max-w-full max-h-[calc(80vh-180px)] object-contain shadow-none rounded-xl"
            :style="activeMode === 'adjust' ? filterStyle : ''"
            crossorigin="anonymous"
            @load="onImageLoad"
          >
        </div>
      </div>

      <!-- Sub Toolbar (Active Mode Tools) -->
      <div class="h-24 border-t border-border bg-card/90 backdrop-blur-md flex items-center justify-center px-6 relative z-10">
        <!-- Crop Tools -->
        <div
          v-if="activeMode === 'crop'"
          class="flex items-center gap-4"
        >
          <div class="flex items-center gap-1 bg-muted/50 p-1 rounded-xl border border-border">
            <Button 
              v-for="preset in cropPresets" 
              :key="preset.label"
              variant="ghost" 
              size="sm"
              class="text-xs h-7 px-3"
              :class="currentAspectRatio === preset.value ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm' : 'text-muted-foreground hover:text-foreground hover:bg-accent'"
              @click="setAspectRatio(preset.value)"
            >
              {{ preset.label }}
            </Button>
          </div>
                    
          <div class="w-px h-8 bg-border" />
                    
          <div class="flex items-center gap-1">
            <Button
              size="sm"
              variant="ghost"
              class="h-8 w-8 p-0 text-muted-foreground hover:text-foreground hover:bg-accent rounded-lg"
              title="Zoom Out"
              @click="zoom(-0.1)"
            >
              <ZoomOut
                class="w-4 h-4"
                stroke-width="1.5"
              />
            </Button>
            <Button
              size="sm"
              variant="ghost"
              class="h-8 w-8 p-0 text-muted-foreground hover:text-foreground hover:bg-accent rounded-lg"
              title="Zoom In"
              @click="zoom(0.1)"
            >
              <ZoomIn
                class="w-4 h-4"
                stroke-width="1.5"
              />
            </Button>
            <Button
              size="sm"
              variant="ghost"
              class="h-8 w-8 p-0 text-muted-foreground hover:text-foreground hover:bg-accent rounded-lg"
              title="Rotate"
              @click="rotate(90)"
            >
              <RotateCw
                class="w-4 h-4"
                stroke-width="1.5"
              />
            </Button>
            <Button
              size="sm"
              variant="ghost"
              class="h-8 w-8 p-0 text-muted-foreground hover:text-foreground hover:bg-accent rounded-lg"
              title="Flip Horizontal"
              @click="flip('horizontal')"
            >
              <FlipHorizontal
                class="w-4 h-4"
                stroke-width="1.5"
              />
            </Button>
            <Button
              size="sm"
              variant="ghost"
              class="h-8 w-8 p-0 text-muted-foreground hover:text-foreground hover:bg-accent rounded-lg"
              title="Flip Vertical"
              @click="flip('vertical')"
            >
              <FlipVertical
                class="w-4 h-4"
                stroke-width="1.5"
              />
            </Button>
          </div>

          <div class="w-px h-8 bg-border" />

          <div class="flex gap-2">
            <Button
              size="sm"
              variant="ghost"
              class="text-muted-foreground hover:text-foreground"
              @click="cancelCrop"
            >
              Cancel
            </Button>
            <Button
              size="sm"
              variant="ghost"
              class="text-muted-foreground hover:text-foreground"
              @click="resetCropView"
            >
              Reset
            </Button>
            <Button
              size="sm"
              class="bg-primary text-primary-foreground hover:bg-primary/90"
              @click="applyCrop"
            >
              Apply Crop
            </Button>
          </div>
        </div>

        <!-- Adjust Tools -->
        <div
          v-if="activeMode === 'adjust'"
          class="flex flex-col md:flex-row items-center gap-6 w-full max-w-4xl"
        >
          <!-- Presets -->
          <div class="flex items-center gap-2 overflow-x-auto max-w-[200px] md:max-w-none no-scrollbar pr-4 border-r border-border mr-2">
            <button 
              v-for="preset in filterPresets" 
              :key="preset.name"
              class="flex flex-col items-center justify-center min-w-[60px] gap-1 group"
              @click="applyPreset(preset)"
            >
              <div class="w-10 h-10 rounded-xl border border-border bg-muted/30 group-hover:bg-accent flex items-center justify-center">
                <component
                  :is="preset.icon"
                  class="w-4 h-4 text-muted-foreground group-hover:text-foreground"
                  stroke-width="1.5"
                />
              </div>
              <span class="text-[10px] text-muted-foreground group-hover:text-foreground">{{ preset.name }}</span>
            </button>
          </div>

          <!-- Sliders -->
          <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 w-full">
            <div class="flex items-center gap-3">
              <span class="text-xs font-medium w-20 text-muted-foreground">Brightness</span>
              <div class="flex-1 relative h-5 flex items-center">
                <input
                  v-model="filters.brightness"
                  type="range"
                  min="0"
                  max="200" 
                  class="w-full h-1 bg-muted rounded-xl appearance-none cursor-pointer accent-primary hover:accent-primary focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground"
                >
              </div>
              <span class="text-xs w-8 text-right text-foreground tabular-nums">{{ filters.brightness }}%</span>
            </div>
            <div class="flex items-center gap-3">
              <span class="text-xs font-medium w-20 text-muted-foreground">Contrast</span>
              <div class="flex-1 relative h-5 flex items-center">
                <input
                  v-model="filters.contrast"
                  type="range"
                  min="0"
                  max="200" 
                  class="w-full h-1 bg-muted rounded-xl appearance-none cursor-pointer accent-primary hover:accent-primary focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground"
                >
              </div>
              <span class="text-xs w-8 text-right text-foreground tabular-nums">{{ filters.contrast }}%</span>
            </div>
            <!-- Saturation -->
            <div class="flex items-center gap-3">
              <span class="text-xs font-medium w-20 text-muted-foreground">Saturation</span>
              <div class="flex-1 relative h-5 flex items-center">
                <input
                  v-model="filters.saturation"
                  type="range"
                  min="0"
                  max="200" 
                  class="w-full h-1 bg-muted rounded-xl appearance-none cursor-pointer accent-primary hover:accent-primary focus:outline-none focus:ring-2 focus:ring-primary/50 text-foreground"
                >
              </div>
              <span class="text-xs w-8 text-right text-foreground tabular-nums">{{ filters.saturation }}%</span>
            </div>
          </div>

          <div class="pl-4 border-l border-border flex flex-col gap-2">
            <Button
              size="sm"
              variant="ghost"
              class="text-muted-foreground hover:text-foreground h-7 text-xs"
              @click="resetFilters"
            >
              Reset
            </Button>
            <Button
              size="sm"
              class="bg-primary text-primary-foreground hover:bg-primary/90"
              @click="applyFilters"
            >
              Apply
            </Button>
          </div>
        </div>

        <!-- Resize Tools -->
        <div
          v-if="activeMode === 'resize'"
          class="flex items-center gap-6"
        >
          <div class="flex items-center gap-3">
            <div class="flex flex-col gap-1">
              <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider px-1">Width</label>
              <input 
                v-model="resizeConfig.width" 
                type="number" 
                class="bg-muted/50 border border-border rounded-xl px-3 py-1.5 text-sm text-foreground w-28 focus:outline-none focus:border-primary/40 focus:ring-1 focus:ring-primary/40 placeholder-muted-foreground/30"
                :placeholder="t('media.modals.editor.widthPlaceholder')"
              >
            </div>
            <span class="text-muted-foreground/30 mt-5">×</span>
            <div class="flex flex-col gap-1">
              <label class="text-[10px] text-muted-foreground uppercase font-bold tracking-wider px-1">Height</label>
              <input 
                v-model="resizeConfig.height" 
                type="number" 
                class="bg-muted/50 border border-border rounded-xl px-3 py-1.5 text-sm text-foreground w-28 focus:outline-none focus:border-primary/40 focus:ring-1 focus:ring-primary/40 placeholder-muted-foreground/30"
                :placeholder="t('media.modals.editor.heightPlaceholder')"
              >
            </div>
            <div class="flex items-end h-full pb-1 ml-2">
              <button 
                class="p-2 rounded-xl border"
                :class="resizeConfig.maintainAspectRatio ? 'bg-primary/10 text-primary border-primary/30' : 'bg-transparent text-muted-foreground/30 border-transparent hover:text-muted-foreground'"
                title="Lock Aspect Ratio"
                @click="resizeConfig.maintainAspectRatio = !resizeConfig.maintainAspectRatio"
              >
                <Lock
                  v-if="resizeConfig.maintainAspectRatio"
                  class="w-4 h-4"
                  stroke-width="1.5"
                />
                <Unlock
                  v-else
                  class="w-4 h-4"
                  stroke-width="1.5"
                />
              </button>
            </div>
          </div>
          <div class="w-px h-10 bg-border mx-2" />
          <Button
            class="bg-primary text-primary-foreground hover:bg-primary/90"
            @click="applyResize"
          >
            Apply Resize
          </Button>
        </div>
                
        <div
          v-if="activeMode === 'view'"
          class="text-sm text-muted-foreground/60"
        >
          Select a tool below to start editing
        </div>
      </div>

      <!-- Main Toolbar (Bottom) -->
      <div class="h-20 bg-card flex items-center justify-center gap-8 md:gap-16 pb-safe border-t border-border z-20">
        <button 
          v-for="mode in modes" 
          :key="mode.id"
          class="flex flex-col items-center gap-1.5 group min-w-[64px] outline-none"
          :disabled="!imageLoaded"
          @click="setMode(mode.id)"
        >
          <div 
            class="p-2.5 rounded-xl relative"
            :class="activeMode === mode.id ? 'bg-primary text-primary-foreground scale-110 shadow-lg' : 'text-muted-foreground hover:text-foreground hover:bg-accent'"
          >
            <component
              :is="mode.icon"
              class="w-5 h-5"
              stroke-width="1.5"
            />
            <div
              v-if="activeMode === mode.id"
              class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-primary opacity-0"
            />
          </div>
          <span 
            class="text-[10px] font-medium tracking-wide transition-colors uppercase"
            :class="activeMode === mode.id ? 'text-primary' : 'text-muted-foreground/60 group-hover:text-muted-foreground'"
          >
            {{ mode.label }}
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, shallowRef, computed, onUnmounted, markRaw, watch, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  Crop,
  Eye,
  FlipHorizontal,
  FlipVertical,
  Lock,
  Moon,
  Palette,
  RotateCw,
  Save,
  Scaling,
  Sliders,
  Sparkles,
  Sun,
  Unlock,
  X,
  ZoomIn,
  ZoomOut,
} from 'lucide-vue-next';
import Cropper from 'cropperjs';
import { MediaService } from '@/modules/Content/Media/services/mediaService';
import { Button } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import type { Media } from '@/modules/Content/Media/types/media';

type EditorMode = 'view' | 'crop' | 'adjust' | 'resize';

interface FilterSettings {
    brightness: number;
    contrast: number;
    saturation: number;
}

interface ResizeConfig {
    width: number;
    height: number;
    maintainAspectRatio: boolean;
    originalRatio: number;
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

// --- State ---
const activeMode = ref<EditorMode>('view'); 
const imageElement = ref<HTMLImageElement | null>(null);
const currentImageSrc = ref(props.media.url || '');
const imageLoaded = ref(false);
const saving = ref(false);
const saveAsNew = ref(false);
const customFilename = ref('');

// Initialize filename from prop
watch(() => props.media.name, (newName) => {
    if (newName) customFilename.value = newName + '_edited';
}, { immediate: true });

const modes = [
    { id: 'view' as EditorMode, label: 'View', icon: Eye },
    { id: 'crop' as EditorMode, label: 'Crop', icon: Crop },
    { id: 'adjust' as EditorMode, label: 'Adjust', icon: Sliders },
    { id: 'resize' as EditorMode, label: 'Resize', icon: Scaling },
];

// --- Cropper State ---
const cropper = shallowRef<Cropper | null>(null);
const cropperReady = ref(false);
const currentAspectRatio = ref(NaN);
const scaleX = ref(1);
const scaleY = ref(1);
const cropPresets = [
    { label: 'Free', value: NaN },
    { label: '1:1', value: 1 },
    { label: '16:9', value: 16/9 },
    { label: '4:3', value: 4/3 },
    { label: '2:3', value: 2/3 },
];

// --- Adjust State ---
const filters = ref<FilterSettings>({
    brightness: 100,
    contrast: 100,
    saturation: 100,
});

const filterPresets = [
    { name: 'Auto', icon: Sparkles, settings: { brightness: 110, contrast: 110, saturation: 115 } },
    { name: 'Warm', icon: Sun, settings: { brightness: 105, contrast: 105, saturation: 120 } },
    { name: 'Mood', icon: Moon, settings: { brightness: 90, contrast: 120, saturation: 80 } },
    { name: 'B&W', icon: Palette, settings: { brightness: 100, contrast: 120, saturation: 0 } },
];

// --- Resize State ---
const resizeConfig = ref<ResizeConfig>({
    width: 0,
    height: 0,
    maintainAspectRatio: true,
    originalRatio: 1
});

// --- Computed ---
const filterStyle = computed(() => {
    return {
        filter: `brightness(${filters.value.brightness}%) contrast(${filters.value.contrast}%) saturate(${filters.value.saturation}%)`
    };
});

const hasChanges = computed(() => {
    // If image source changed (e.g. it's now a data URL from crop/resize/filter)
    const isSourceChanged = currentImageSrc.value !== props.media.url;
    
    // If we have pending filter changes in adjust mode
    const pendingFilters = activeMode.value === 'adjust' && isFilterDirty();
    
    // If Save As New is toggled
    const isSaveAsNew = saveAsNew.value;

    return isSourceChanged || pendingFilters || isSaveAsNew;
});

// --- Methods ---

const onImageLoad = (e: Event) => {
    imageLoaded.value = true;
    const img = e.target as HTMLImageElement;
    if (img.naturalWidth) {
        resizeConfig.value.width = img.naturalWidth;
        resizeConfig.value.height = img.naturalHeight;
        resizeConfig.value.originalRatio = img.naturalWidth / img.naturalHeight;
    }
};

const handleEnterKey = () => {
    if (activeMode.value === 'crop') {
        applyCrop();
    }
    else if (activeMode.value === 'adjust') {
        applyFilters();
    }
    else if (activeMode.value === 'resize') {
        applyResize();
    }
    else if (activeMode.value === 'view') {
        saveImage();
    }
};

const setMode = async (mode: EditorMode) => {
    if (activeMode.value === mode) return;

    if (activeMode.value === 'crop') {
        destroyCropper();
    }
    
    // If switching FROM Adjust TO Crop, apply the filters first
    if (activeMode.value === 'adjust' && mode === 'crop') {
        if (isFilterDirty()) {
            await applyFilters();
        }
    }

    activeMode.value = mode;

    if (mode === 'crop') {
        await nextTick();
        initCropper();
    }
};

// --- Crop Logic ---
const initCropper = () => {
    if (!imageElement.value || cropper.value) return;
    
    cropperReady.value = false;
    
    const cropperInstance = new Cropper(imageElement.value, {
        container: imageElement.value.parentElement ?? undefined,
    });
    
    cropper.value = markRaw(cropperInstance);
    const selection = cropperInstance.getCropperSelection();
    if (selection) {
        selection.aspectRatio = currentAspectRatio.value;
        selection.initialCoverage = 0.8;
        selection.$center().$render();
        cropperReady.value = true;
    }
};

const destroyCropper = () => {
    if (cropper.value) {
        cropper.value.destroy();
        cropper.value = null;
    }
    cropperReady.value = false;
    scaleX.value = 1;
    scaleY.value = 1;
};

const setAspectRatio = (ratio: number) => {
    currentAspectRatio.value = ratio;
    if (cropper.value && cropperReady.value) {
        const selection = cropper.value.getCropperSelection();
        if (!selection) return;
        selection.aspectRatio = ratio;
        selection.$center().$render();
    }
};

const rotate = (deg: number) => {
    if (!cropper.value || !cropperReady.value) return;
    const cropperImage = cropper.value.getCropperImage();
    if (!cropperImage) return;
    cropperImage.$rotate(`${deg}deg`);
};

const zoom = (delta: number) => {
    if (!cropper.value || !cropperReady.value) return;
    const cropperImage = cropper.value.getCropperImage();
    if (!cropperImage) return;
    cropperImage.$zoom(delta);
};

const flip = (dir: 'horizontal' | 'vertical') => {
    if (!cropper.value || !cropperReady.value) return;
    const cropperImage = cropper.value.getCropperImage();
    if (!cropperImage) return;

    if (dir === 'horizontal') scaleX.value = scaleX.value === -1 ? 1 : -1;
    if (dir === 'vertical') scaleY.value = scaleY.value === -1 ? 1 : -1;
    cropperImage.$scale(scaleX.value, scaleY.value);
};

const resetCropView = () => {
    if (!cropper.value || !cropperReady.value) return;
    const cropperImage = cropper.value.getCropperImage();
    const selection = cropper.value.getCropperSelection();
    if (!cropperImage || !selection) return;

    scaleX.value = 1;
    scaleY.value = 1;
    cropperImage.$resetTransform();
    cropperImage.$scale(scaleX.value, scaleY.value);
    selection.$reset().$center().$render();
};

const applyCrop = async (): Promise<void> => {
    if (!cropper.value || !cropperReady.value) return;

    const selection = cropper.value.getCropperSelection();
    if (!selection) {
        logger.error("Failed to get cropped canvas");
        return;
    }

    const canvas = await selection.$toCanvas();
    currentImageSrc.value = canvas.toDataURL(props.media.mime_type || 'image/png');

    resizeConfig.value.width = canvas.width;
    resizeConfig.value.height = canvas.height;
    resizeConfig.value.originalRatio = canvas.width / canvas.height;

    await setMode('view');
};

const cancelCrop = () => {
    setMode('view');
};

// --- Adjust Logic ---
const applyPreset = (preset: { settings: FilterSettings }) => {
    filters.value = { ...preset.settings };
};

const isFilterDirty = () => {
    return filters.value.brightness !== 100 || 
           filters.value.contrast !== 100 || 
           filters.value.saturation !== 100;
};

const resetFilters = () => {
    filters.value = { brightness: 100, contrast: 100, saturation: 100 };
};

const applyFilters = async (): Promise<void> => {
    if (!imageElement.value) return;

    return new Promise((resolve) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.src = currentImageSrc.value;
        
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            if (ctx) {
                canvas.width = img.width;
                canvas.height = img.height;
                
                ctx.filter = `brightness(${filters.value.brightness}%) contrast(${filters.value.contrast}%) saturate(${filters.value.saturation}%)`;
                ctx.drawImage(img, 0, 0);
                
                currentImageSrc.value = canvas.toDataURL(props.media.mime_type || 'image/png');
                resetFilters(); 
            }
            resolve();
        };
    });
};

// --- Resize Logic ---
watch(() => resizeConfig.value.width, (newWidth) => {
    if (activeMode.value !== 'resize' || !resizeConfig.value.maintainAspectRatio) return;
    resizeConfig.value.height = Math.round(newWidth / resizeConfig.value.originalRatio);
});

watch(() => resizeConfig.value.height, (newHeight) => {
    if (activeMode.value !== 'resize' || !resizeConfig.value.maintainAspectRatio) return;
    resizeConfig.value.width = Math.round(newHeight * resizeConfig.value.originalRatio);
});

const applyResize = () => {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.src = currentImageSrc.value;
    
    img.onload = () => {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        if (ctx) {
            canvas.width = resizeConfig.value.width;
            canvas.height = resizeConfig.value.height;
            
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            
            currentImageSrc.value = canvas.toDataURL(props.media.mime_type || 'image/png');
        }
        setMode('view');
    };
};

// --- Image Helpers ---

function base64ToBlob(base64: string) {
  const [metadata, content] = base64.split(';base64,');
  const contentType = metadata?.split(':')[1] || 'image/png';
  const raw = window.atob(content || '');
  const rawLength = raw.length;
  const uInt8Array = new Uint8Array(rawLength);

  for (let i = 0; i < rawLength; ++i) {
    uInt8Array[i] = raw.charCodeAt(i);
  }

  return new Blob([uInt8Array], { type: contentType });
}

const getSecureBlob = async () => {
    // If it's already a base64 string, we can convert directly
    if (currentImageSrc.value.startsWith('data:')) {
        return base64ToBlob(currentImageSrc.value);
    }

    // Otherwise (HTTP URL), we need to draw it to a canvas to get the data
    if (!imageElement.value) throw new Error("Image element not found");

    const canvas = document.createElement('canvas');
    canvas.width = imageElement.value.naturalWidth;
    canvas.height = imageElement.value.naturalHeight;
    const ctx = canvas.getContext('2d');
    
    if (ctx) {
        ctx.drawImage(imageElement.value, 0, 0);
        const dataLink = canvas.toDataURL(props.media.mime_type || 'image/png');
        return base64ToBlob(dataLink);
    }
    throw new Error("Failed to get 2D context");
};

// --- Save Final ---
const saveImage = async () => {
    saving.value = true;
    try {
        if (activeMode.value === 'adjust' && isFilterDirty()) await applyFilters();
        if (activeMode.value === 'crop') await applyCrop();

        // Get blob securely (whether it's base64 or url)
        const blob = await getSecureBlob();
        
        if (blob.size === 0) throw new Error("Generated image is empty");
        
        const formData = new FormData();
        const fileName = props.media.file_name || 'edited-image.png';
        const file = new File([blob], fileName, { type: blob.type });
        
        formData.append('image', file);
        formData.append('save_as_new', saveAsNew.value ? '1' : '0');
        if (saveAsNew.value && customFilename.value) {
            formData.append('custom_filename', customFilename.value);
        }

        await MediaService.edit(String(props.media.id), formData);
        emit('updated');
        emit('close');
    } catch (err: unknown) {
        logger.error("Failed to save", err);
        toast.error.fromResponse(err);
    } finally {
        saving.value = false;
    }
};

onUnmounted(() => {
    destroyCropper();
});
</script>

<style scoped>
input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<style>
/* cropperjs uses image-orientation: 0deg; strict engines reject the value — none is equivalent here */
.cropper-container img {
    image-orientation: none;
}
</style>
