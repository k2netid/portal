<template>
  <div class="space-y-4 p-5 rounded-2xl bg-card border border-border/70 shadow-sm">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <Sparkles class="w-4 h-4 text-primary" />
        <h4 class="text-sm font-bold text-foreground">
          {{ t('publishing.theme_customizer.presets.title', 'Skema Warna Siap Pakai') }}
        </h4>
      </div>
      <Badge
        variant="outline"
        class="text-[10px] font-semibold px-2 py-0.5"
      >
        {{ t('publishing.theme_customizer.presets.count', { count: Object.keys(JANARI_PRESETS).length }, `${Object.keys(JANARI_PRESETS).length} Presets`) }}
      </Badge>
    </div>

    <p class="text-xs text-muted-foreground leading-relaxed">
      {{ t('publishing.theme_customizer.presets.hint', 'Pilih palet warna terkurasi untuk menerapkan harmoni warna di seluruh komponen tema secara instan.') }}
    </p>

    <!-- Preset Swatches Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 pt-1">
      <button
        v-for="(preset, key) in JANARI_PRESETS"
        :key="key"
        type="button"
        class="group relative flex flex-col p-2.5 rounded-xl border text-left transition-all duration-200 hover:shadow-md hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-primary/40"
        :class="[
          activePresetKey === key
            ? 'border-primary bg-primary/5 ring-1 ring-primary shadow-sm'
            : 'border-border/60 bg-background/50 hover:border-border hover:bg-muted/30'
        ]"
        @click="applyPreset(key, preset)"
      >
        <!-- Dual Color Swatches -->
        <div class="w-full h-7 rounded-lg overflow-hidden flex border border-border/40 mb-2 shadow-inner">
          <div
            class="flex-1 transition-transform group-hover:scale-105"
            :style="{ backgroundColor: preset.light }"
            :title="`Light: ${preset.light}`"
          />
          <div
            class="flex-1 transition-transform group-hover:scale-105"
            :style="{ backgroundColor: preset.dark }"
            :title="`Dark: ${preset.dark}`"
          />
        </div>

        <!-- Preset Label & Status -->
        <div class="flex items-center justify-between w-full">
          <span class="text-[11px] font-semibold capitalize text-foreground truncate pr-1">
            {{ formatPresetName(key) }}
          </span>
          <div
            v-if="activePresetKey === key"
            class="w-3.5 h-3.5 rounded-full bg-primary text-primary-foreground flex items-center justify-center shrink-0"
          >
            <Check class="w-2.5 h-2.5" />
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Sparkles, Check } from 'lucide-vue-next';
import { Badge } from '@/shared/components/ui';
import { JANARI_PRESETS, type JanariPresetKey } from '@/modules/Content/Layout/config/janariPresets';

const props = defineProps<{
  currentPreset?: string;
  currentColorPrimary?: string;
}>();

const emit = defineEmits<{
  (e: 'selectPreset', key: string, values: { color_preset: string; color_primary?: string }): void;
}>();

const { t } = useI18n();

const activePresetKey = computed(() => {
  if (props.currentPreset && props.currentPreset !== 'custom') {
    return props.currentPreset;
  }
  // Try matching primary color with presets
  if (props.currentColorPrimary) {
    const hex = props.currentColorPrimary.toLowerCase();
    for (const [key, preset] of Object.entries(JANARI_PRESETS)) {
      if (preset.light.toLowerCase() === hex || preset.dark.toLowerCase() === hex) {
        return key;
      }
    }
  }
  return '';
});

function formatPresetName(key: string): string {
  return key.replace(/_/g, ' ');
}

function applyPreset(key: string, preset: (typeof JANARI_PRESETS)[JanariPresetKey]) {
  emit('selectPreset', key, {
    color_preset: key,
    color_primary: preset.light,
  });
}
</script>
