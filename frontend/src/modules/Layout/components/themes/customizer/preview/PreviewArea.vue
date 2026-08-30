<template>
  <div class="flex-1 min-h-0 bg-muted/40 h-full overflow-hidden flex flex-col relative transition-colors">
    <!-- Device Toolbar -->
    <div class="h-12 bg-background/90 backdrop-blur-sm border-b flex items-center justify-between px-4 shadow-sm z-20 shrink-0">
      <div class="flex items-center gap-1.5">
        <button
          v-for="mode in deviceModes"
          :key="mode.id"
          type="button"
          :class="activeDevice === mode.id ? 'bg-primary/10 text-primary ring-1 ring-primary/20' : 'text-muted-foreground hover:bg-muted'"
          class="p-2 rounded-md transition-colors flex items-center gap-2"
          :title="$t('publishing.theme_customizer.editor.preview.devices.' + mode.id)"
          @click="activeDevice = mode.id"
        >
          <component
            :is="mode.icon"
            class="w-4 h-4"
          />
          <span class="text-xs font-medium hidden lg:block">{{ $t('publishing.theme_customizer.editor.preview.devices.' + mode.id) }}</span>
        </button>
      </div>

      <button
        type="button"
        :disabled="isRefreshing"
        class="p-2 text-muted-foreground hover:text-primary hover:bg-muted rounded-md transition-colors flex items-center gap-2 bg-background border hover:border-primary/50"
        :title="$t('publishing.theme_customizer.editor.preview.refresh')"
        @click="refreshPreview"
      >
        <RotateCcw
          class="w-4 h-4"
          :class="{'animate-spin': isRefreshing}"
        />
        <span class="text-xs font-medium hidden sm:block">{{ $t('publishing.theme_customizer.editor.preview.refresh') }}</span>
      </button>
    </div>

    <p
      v-if="props.enableClickSelect"
      class="px-4 py-1.5 text-[11px] text-muted-foreground border-b bg-muted/20 shrink-0"
    >
      {{ $t('publishing.theme_customizer.bridge.preview_hint') }}
    </p>

    <!-- Fill remaining height; device frame scrolls only for tablet/mobile chrome -->
    <div
      class="flex-1 min-h-0 flex justify-center"
      :class="activeDevice === 'desktop' ? 'items-stretch p-0' : 'items-start overflow-auto p-4 md:p-6 custom-scrollbar'"
    >
      <div
        class="relative bg-background overflow-hidden flex flex-col min-h-0"
        :class="[
          activeDevice === 'desktop'
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
          class="w-full flex-1 min-h-0 bg-background"
          @select-target="(payload) => emit('select-target', payload)"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import {
  BatteryFull,
  MonitorIcon,
  RotateCcw,
  SmartphoneIcon,
  TabletIcon,
  Wifi,
} from 'lucide-vue-next';
import ThemePreview from '../../ThemePreview.vue';
import type { Theme } from '@/modules/Layout/types/theme';

const props = defineProps<{
  previewTheme: Theme;
  previewUrl?: string;
  enableClickSelect?: boolean;
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

const previewStyles = computed(() => {
  switch (activeDevice.value) {
    case 'mobile':
      return { width: '375px', height: '720px' };
    case 'tablet':
      return { width: '768px', height: '900px' };
    default:
      return { width: '100%', height: '100%' };
  }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: 4px;
}
</style>
