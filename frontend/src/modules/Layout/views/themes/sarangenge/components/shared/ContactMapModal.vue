<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open && mapEnabled"
        class="fixed inset-0 z-[400] flex items-end sm:items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
        @click.self="close"
      >
        <div
          class="sarangenge-panel w-full max-w-2xl p-5 sm:p-6 space-y-4 shadow-2xl bg-card border border-border/80 rounded-[var(--sarangenge-radius,1.25rem)] animate-in fade-in zoom-in-95 duration-200"
          role="dialog"
          aria-modal="true"
          :aria-label="t('pages.contact.mapTitle', 'Peta Lokasi Kampus')"
        >
          <!-- Modal Header -->
          <div class="flex items-start justify-between gap-4 pb-3 border-b border-border/60">
            <div class="flex items-start gap-3 min-w-0">
              <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 mt-0.5">
                <MapPin class="w-5 h-5" />
              </div>
              <div class="min-w-0">
                <h3 class="font-heading font-bold text-base sm:text-lg text-foreground truncate">
                  {{ schoolName || t('pages.contact.mapTitle', 'Peta Lokasi Kampus') }}
                </h3>
                <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed mt-0.5 break-words">
                  {{ address }}
                </p>
              </div>
            </div>
            <button
              type="button"
              class="w-8 h-8 rounded-lg flex items-center justify-center text-muted-foreground hover:text-foreground hover:bg-muted/60 transition-colors shrink-0"
              :aria-label="t('pages.contact.closeMap', 'Tutup')"
              @click="close"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <!-- Embedded Map Viewport -->
          <div class="relative w-full h-64 sm:h-80 rounded-[calc(var(--sarangenge-radius,1.25rem)-0.375rem)] overflow-hidden border border-border/60 bg-muted/30">
            <div
              v-if="!iframeReady"
              class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-muted/40 text-muted-foreground text-xs"
            >
              <Loader2 class="w-6 h-6 animate-spin text-primary" />
              <span>{{ t('pages.contact.loadingMap', 'Memuat peta lokasi...') }}</span>
            </div>
            <iframe
              v-if="iframeReady && mapEmbedUrl"
              :src="mapEmbedUrl"
              class="w-full h-full border-0"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              :title="t('pages.contact.mapTitle', 'Peta Lokasi Kampus')"
            />
          </div>

          <!-- Modal Actions Footer -->
          <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <div class="flex flex-wrap gap-2">
              <Button
                type="button"
                variant="primary"
                size="sm"
                class="font-semibold shadow-sm"
                @click="openExternal"
              >
                <ExternalLink class="w-3.5 h-3.5 mr-1.5" />
                {{ t('pages.contact.openMap', 'Buka di Google Maps') }}
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                class="font-semibold"
                @click="openDirections"
              >
                <Navigation class="w-3.5 h-3.5 mr-1.5" />
                {{ t('pages.contact.getDirections', 'Petunjuk arah') }}
              </Button>
            </div>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              class="text-xs text-muted-foreground hover:text-foreground"
              @click="close"
            >
              {{ t('pages.contact.closeMap', 'Tutup') }}
            </Button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, onUnmounted } from 'vue';
import { MapPin, X, ExternalLink, Navigation, Loader2 } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

const props = withDefaults(
  defineProps<{
    open: boolean;
    address: string;
    schoolName?: string;
    mapEmbedUrl?: string;
    mapEnabled?: boolean;
  }>(),
  {
    open: false,
    schoolName: '',
    mapEmbedUrl: '',
    mapEnabled: true,
  },
);

const emit = defineEmits<{
  (e: 'update:open', val: boolean): void;
  (e: 'openExternal'): void;
  (e: 'openDirections'): void;
}>();

const { t } = useThemeI18n('sarangenge');
const iframeReady = ref(false);
let iframeTimer: ReturnType<typeof setTimeout> | undefined;

const close = () => {
  emit('update:open', false);
};

const openExternal = () => {
  emit('openExternal');
};

const openDirections = () => {
  emit('openDirections');
};

const onKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape') {
    close();
  }
};

watch(
  () => props.open,
  (isOpen) => {
    if (typeof document === 'undefined') return;
    if (isOpen) {
      document.addEventListener('keydown', onKeydown);
      document.body.style.overflow = 'hidden';
      if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(() => {
          iframeReady.value = true;
        }, { timeout: 400 });
      } else {
        iframeTimer = setTimeout(() => {
          iframeReady.value = true;
        }, 50);
      }
      return;
    }
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
    iframeReady.value = false;
  },
);

onUnmounted(() => {
  if (iframeTimer) clearTimeout(iframeTimer);
  if (typeof document !== 'undefined') {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
  }
});
</script>
