<template>
  <div class="flex-1 min-h-0 bg-muted/40 h-full overflow-hidden flex flex-col relative transition-colors">
    <!-- Device & Scale Toolbar -->
    <div class="h-11 bg-background/95 backdrop-blur-sm border-b border-border flex items-center justify-between px-3.5 shadow-xs z-20 shrink-0 gap-3">
      <!-- Left: Device Viewports -->
      <div class="flex items-center gap-1 rounded-xl border border-border bg-muted/40 p-1">
        <button
          v-for="mode in deviceModes"
          :key="mode.id"
          type="button"
          :class="activeDevice === mode.id ? 'bg-background text-foreground shadow-xs font-semibold' : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'"
          class="h-7 w-7 rounded-lg transition-all flex items-center justify-center"
          :title="$t('publishing.theme_customizer.editor.preview.devices.' + mode.id)"
          :aria-label="$t('publishing.theme_customizer.editor.preview.devices.' + mode.id)"
          @click="selectDevice(mode.id)"
        >
          <component
            :is="mode.icon"
            class="w-3.5 h-3.5"
          />
        </button>
      </div>

      <!-- Center: Scale / Zoom Slider & Presets -->
      <div class="flex items-center gap-2">
        <!-- Zoom Out Button -->
        <button
          type="button"
          class="h-7 w-7 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted flex items-center justify-center transition-colors disabled:opacity-30"
          :disabled="zoomLevel <= 50"
          title="Zoom Out (-10%)"
          aria-label="Zoom Out"
          @click="zoomOut"
        >
          <ZoomOut class="w-3.5 h-3.5" />
        </button>

        <!-- Slider Range -->
        <div class="flex items-center gap-1.5 w-24 sm:w-32">
          <input
            v-model.number="zoomLevel"
            type="range"
            min="50"
            max="150"
            step="5"
            class="w-full h-1.5 bg-muted rounded-lg appearance-none cursor-pointer accent-primary focus:outline-none"
            title="Skala Ukuran Layar"
            aria-label="Skala Ukuran Layar"
          />
        </div>

        <!-- Zoom In Button -->
        <button
          type="button"
          class="h-7 w-7 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted flex items-center justify-center transition-colors disabled:opacity-30"
          :disabled="zoomLevel >= 150"
          title="Zoom In (+10%)"
          aria-label="Zoom In"
          @click="zoomIn"
        >
          <ZoomIn class="w-3.5 h-3.5" />
        </button>

        <!-- Zoom Preset Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <button
              type="button"
              class="h-7 px-2 text-xs font-mono font-medium text-muted-foreground hover:text-foreground hover:bg-muted border border-border/70 rounded-lg flex items-center gap-1 transition-colors"
              title="Pilihan Skala Layar"
            >
              <span>{{ zoomLevel }}%</span>
              <ChevronDown class="w-3 h-3 opacity-60" />
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            align="center"
            class="w-32 rounded-xl"
          >
            <DropdownMenuItem
              v-for="preset in zoomPresets"
              :key="preset"
              :class="{'font-bold text-primary': zoomLevel === preset}"
              @click="setZoom(preset)"
            >
              {{ preset }}%
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="resetZoom">
              <RotateCcw class="w-3.5 h-3.5 mr-1.5" />
              100% (Reset)
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Right: Refresh Button -->
      <div class="flex items-center gap-1.5">
        <button
          type="button"
          :disabled="isRefreshing"
          class="h-8 w-8 text-muted-foreground hover:text-foreground hover:bg-muted rounded-xl transition-all flex items-center justify-center bg-background border border-border shadow-xs hover:border-primary/40 disabled:opacity-50"
          :title="$t('publishing.theme_customizer.editor.preview.refresh')"
          :aria-label="$t('publishing.theme_customizer.editor.preview.refresh')"
          @click="refreshPreview"
        >
          <RotateCcw
            class="w-3.5 h-3.5"
            :class="{'animate-spin': isRefreshing}"
          />
        </button>
      </div>
    </div>

    <p
      v-if="props.enableClickSelect"
      class="px-4 py-1.5 text-[11px] text-muted-foreground border-b bg-muted/20 shrink-0"
    >
      {{ $t('publishing.theme_customizer.bridge.preview_hint') }}
    </p>

    <!-- Canvas Scrollable Stage -->
    <div
      class="flex-1 min-h-0 overflow-auto flex justify-center custom-scrollbar"
      :class="activeDevice === 'desktop' && zoomLevel === 100 ? 'items-stretch p-0' : 'items-start p-4 md:p-6'"
    >
      <div
        class="flex flex-col items-center justify-start shrink-0 transition-all duration-150"
        :style="stageWrapperStyle"
      >
        <div
          class="relative bg-background overflow-hidden flex flex-col min-h-0 origin-top transition-transform duration-150"
          :class="[
            activeDevice === 'desktop' && zoomLevel === 100
              ? 'w-full h-full rounded-none border-0 shadow-none'
              : 'shadow-2xl rounded-[2.5rem] border-[14px] border-slate-900 dark:border-slate-800 shrink-0',
          ]"
          :style="previewStyles"
        >
          <div
            v-if="activeDevice !== 'desktop'"
            class="h-7 w-full shrink-0 z-20 flex items-center justify-between px-6 bg-slate-900 dark:bg-slate-800"
          >
            <div class="text-[10px] font-medium text-slate-400">
              9:41
            </div>
            <div class="flex gap-1.5">
              <Wifi class="w-3 h-3 text-slate-400" />
              <BatteryFull class="w-3 h-3 text-slate-400" />
            </div>
          </div>

          <ThemePreview
            ref="themePreviewRef"
            :theme="props.previewTheme"
            :preview-url="props.previewUrl"
            :enable-click-select="props.enableClickSelect"
            :focus-target="props.focusTarget"
            class="w-full flex-1 min-h-0 bg-background"
            @select-target="(payload) => emit('select-target', payload)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import {
  BatteryFull,
  ChevronDown,
  MonitorIcon,
  RotateCcw,
  SmartphoneIcon,
  TabletIcon,
  Wifi,
  ZoomIn,
  ZoomOut,
} from 'lucide-vue-next';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
} from '@/shared/components/ui';
import ThemePreview from '../../ThemePreview.vue';
import type { Theme } from '@/modules/Layout/types/theme';

const props = defineProps<{
  previewTheme: Theme;
  previewUrl?: string;
  enableClickSelect?: boolean;
  focusTarget?: string | null;
}>();

const emit = defineEmits<{
  (e: 'select-target', payload: { target: string; mode?: 'design' | 'bindings' }): void;
}>();

const themePreviewRef = ref<{ refreshPreview: () => void } | null>(null);
const isRefreshing = ref(false);

const refreshPreview = async () => {
  isRefreshing.value = true;
  themePreviewRef.value?.refreshPreview?.();
  setTimeout(() => {
    isRefreshing.value = false;
  }, 800);
};

const activeDevice = ref<'desktop' | 'tablet' | 'mobile'>('desktop');
const deviceModes = [
  { id: 'desktop' as const, icon: MonitorIcon },
  { id: 'tablet' as const, icon: TabletIcon },
  { id: 'mobile' as const, icon: SmartphoneIcon },
];

const zoomLevel = ref<number>(100);
const zoomPresets = [50, 75, 90, 100, 110, 125, 150];

const selectDevice = (device: 'desktop' | 'tablet' | 'mobile') => {
  activeDevice.value = device;
};

const zoomIn = () => {
  zoomLevel.value = Math.min(150, zoomLevel.value + 10);
};

const zoomOut = () => {
  zoomLevel.value = Math.max(50, zoomLevel.value - 10);
};

const setZoom = (preset: number) => {
  zoomLevel.value = preset;
};

const resetZoom = () => {
  zoomLevel.value = 100;
};

const stageWrapperStyle = computed(() => {
  const scale = zoomLevel.value / 100;
  if (activeDevice.value === 'desktop' && zoomLevel.value === 100) {
    return { width: '100%', height: '100%' };
  }

  let baseW = 1280;
  let baseH = 900;
  if (activeDevice.value === 'tablet') {
    baseW = 768 + 28;
    baseH = 1024 + 28;
  } else if (activeDevice.value === 'mobile') {
    baseW = 390 + 28;
    baseH = 844 + 28;
  }

  return {
    width: `${baseW * scale}px`,
    height: `${baseH * scale}px`,
    minHeight: `${baseH * scale}px`,
  };
});

const previewStyles = computed(() => {
  const scale = zoomLevel.value / 100;
  let baseW = '1280px';
  let baseH = '900px';

  if (activeDevice.value === 'mobile') {
    baseW = '390px';
    baseH = '844px';
  } else if (activeDevice.value === 'tablet') {
    baseW = '768px';
    baseH = '1024px';
  } else if (zoomLevel.value === 100) {
    return {
      width: '100%',
      height: '100%',
      transform: 'none',
    };
  }

  return {
    width: baseW,
    height: baseH,
    transform: `scale(${scale}) translateZ(0)`,
    transformOrigin: 'top center',
  };
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.25);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.4);
}
</style>

