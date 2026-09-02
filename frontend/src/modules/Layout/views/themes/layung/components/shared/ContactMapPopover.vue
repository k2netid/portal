<template>
  <button
    v-if="asCard && address.trim()"
    type="button"
    class="layung-contact-card contact-info-item w-full"
    :disabled="!mapEnabled"
    :aria-label="`${label}. ${t('pages.contact.viewMap', 'Lihat peta')}`"
    v-bind="$attrs"
    @click="openPopover"
  >
    <span class="layung-contact-card__icon text-sky-500">
      <MapPin class="w-4 h-4" />
    </span>
    <span class="min-w-0 flex-1 text-left">
      <span class="layung-contact-card__label">{{ label }}</span>
      <span class="layung-contact-card__value mt-0.5 block leading-relaxed">{{ address }}</span>
      <span
        v-if="mapEnabled"
        class="mt-1.5 inline-flex text-[10px] font-bold text-sky-600 dark:text-sky-400"
      >
        {{ t('pages.contact.viewMap', 'Lihat peta') }}
      </span>
    </span>
  </button>

  <div
    v-else-if="address.trim()"
    class="space-y-1.5"
  >
    <p class="leading-relaxed">
      {{ address }}
    </p>
    <button
      v-if="mapEnabled"
      type="button"
      class="text-[10px] font-bold text-sky-600 dark:text-sky-400 hover:underline"
      @click="openPopover"
    >
      {{ t('pages.contact.viewMap', 'Lihat peta') }}
    </button>
  </div>

  <Teleport to="body">
      <div
        v-if="open && mapEnabled"
        class="fixed inset-0 z-[400] flex items-end sm:items-center justify-center p-4 bg-slate-950/45"
        @click.self="closePopover"
      >
        <div class="layung-panel w-full max-w-xl p-4 space-y-3 shadow-2xl">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground font-mono">
                {{ label }}
              </p>
              <p class="text-sm font-medium text-foreground leading-relaxed mt-1">
                {{ address }}
              </p>
            </div>
            <button
              type="button"
              class="text-muted-foreground hover:text-foreground text-lg leading-none px-1"
              :aria-label="t('pages.contact.closeMap', 'Tutup peta')"
              @click="closePopover"
            >
              ×
            </button>
          </div>
          <div class="rounded-xl overflow-hidden border border-border bg-muted/20">
            <iframe
              v-if="iframeReady"
              :src="mapEmbedUrl"
              class="w-full h-56"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              :title="t('pages.contact.mapTitle', 'Peta lokasi')"
            />
          </div>
          <div class="flex flex-wrap gap-2">
            <Button
              type="button"
              size="sm"
              variant="outline"
              @click="openMapExternal"
            >
              {{ t('pages.contact.openMap', 'Buka di Google Maps') }}
            </Button>
            <Button
              type="button"
              size="sm"
              variant="outline"
              @click="openMapDirections"
            >
              {{ t('pages.contact.getDirections', 'Petunjuk arah') }}
            </Button>
          </div>
        </div>
      </div>
    </Teleport>
</template>

<script setup lang="ts">
import { computed, onUnmounted, ref, watch } from 'vue';
import { MapPin } from 'lucide-vue-next';
import { Button } from '@/modules/Layout/views/themes/layung/ui';
import { useThemeContactMap } from '@/modules/Layout/composables/useThemeContactMap';
import { useThemeI18n } from '@/modules/Layout/composables/useThemeI18n';

defineOptions({ inheritAttrs: false });

const props = withDefaults(
  defineProps<{
    address: string;
    label: string;
    useDirectLink?: boolean;
    asCard?: boolean;
  }>(),
  {
    useDirectLink: false,
    asCard: false,
  },
);

const { t } = useThemeI18n('layung');
const addressRef = computed(() => props.address);
const { mapEnabled, mapEmbedUrl, openMapExternal, openMapDirections } = useThemeContactMap(
  addressRef,
  { useDirectLink: props.useDirectLink },
);

const open = ref(false);
const iframeReady = ref(false);
let iframeTimer: ReturnType<typeof setTimeout> | undefined;

const onEscape = (event: KeyboardEvent) => {
  if (event.key === 'Escape') closePopover();
};

const openPopover = () => {
  if (!mapEnabled.value) return;
  open.value = true;
  if (typeof window !== 'undefined' && typeof window.requestIdleCallback === 'function') {
    window.requestIdleCallback(() => {
      iframeReady.value = true;
    }, { timeout: 800 });
  } else {
    iframeTimer = setTimeout(() => {
      iframeReady.value = true;
    }, 0);
  }
};

const closePopover = () => {
  open.value = false;
};

watch(open, (isOpen) => {
  if (typeof document === 'undefined') return;
  if (isOpen) {
    document.addEventListener('keydown', onEscape);
    document.body.style.overflow = 'hidden';
    return;
  }
  document.removeEventListener('keydown', onEscape);
  document.body.style.overflow = '';
  iframeReady.value = false;
});

onUnmounted(() => {
  if (iframeTimer) clearTimeout(iframeTimer);
  if (typeof document !== 'undefined') {
    document.removeEventListener('keydown', onEscape);
    document.body.style.overflow = '';
  }
});
</script>
