<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-md max-w-lg">
      <DialogHeader>
        <DialogTitle>
          {{ t('infra.plugins.modal.settings_title', { name: plugin?.name }) }}
        </DialogTitle>
      </DialogHeader>

      <div
        v-if="loadingSlots"
        class="py-8 text-center text-sm text-muted-foreground"
      >
        {{ t('infra.plugins.loading') }}
      </div>

      <div
        v-else
        class="space-y-4 py-2"
      >
        <div>
          <h4 class="text-sm font-semibold text-foreground">
            {{ t('infra.plugins.themeBlocks.title') }}
          </h4>
          <p class="text-xs text-muted-foreground mt-1">
            {{ t('infra.plugins.themeBlocks.hint') }}
          </p>
        </div>

        <div
          v-if="availableSlots.length === 0"
          class="text-sm text-muted-foreground italic"
        >
          {{ t('infra.plugins.themeBlocks.noSlots') }}
        </div>

        <div
          v-else
          class="space-y-2"
        >
          <label
            v-for="slot in availableSlots"
            :key="slot.id"
            class="flex items-start gap-3 rounded-md border border-border px-3 py-2 cursor-pointer hover:bg-muted/40"
          >
            <input
              v-model="selectedSlots"
              type="checkbox"
              class="mt-1"
              :value="slot.id"
            >
            <span>
              <span class="text-sm font-medium text-foreground">{{ slot.label }}</span>
              <span class="block text-xs text-muted-foreground font-mono">{{ slot.id }}</span>
            </span>
          </label>
        </div>
      </div>

      <DialogFooter>
        <Button
          variant="outline"
          :disabled="saving"
          size="sm" class="h-10" @click="$emit('close')"
        >
          {{ t('infra.plugins.modal.close') }}
        </Button>
        <Button
          :disabled="saving || loadingSlots"
          @click="save"
        >
          {{ t('infra.plugins.themeBlocks.save') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { layoutPaths, systemPaths } from '@/engine/api/paths';
import { logger } from '@/shared/utils/logger';
import { useToast } from '@/shared/composables/useToast';
import {
  Button,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/shared/components/ui';

const { t } = useI18n();
const toast = useToast();

interface Plugin {
  id: string;
  name: string;
  slug: string;
  settings?: Record<string, unknown>;
}

interface ThemeSlotOption {
  id: string;
  label: string;
  maxBlocks?: number;
}

const props = defineProps<{
  plugin?: Plugin | null;
}>();

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'saved'): void;
}>();

const loadingSlots = ref(true);
const saving = ref(false);
const availableSlots = ref<ThemeSlotOption[]>([]);
const selectedSlots = ref<string[]>([]);

function readSlotsFromSettings(settings?: Record<string, unknown>): string[] {
  const blocks = settings?.theme_blocks;
  if (!Array.isArray(blocks)) return [];
  const out: string[] = [];
  for (const block of blocks) {
    if (typeof block === 'string') out.push(block);
    else if (block && typeof block === 'object' && 'slot' in block && typeof (block as { slot: string }).slot === 'string') {
      out.push((block as { slot: string }).slot);
    }
  }
  return out;
}

async function loadSlots() {
  loadingSlots.value = true;
  try {
    const response = await api.get(layoutPaths.pluginThemeSlots);
    const data = (response.data ?? response) as { slots?: ThemeSlotOption[] };
    availableSlots.value = Array.isArray(data.slots) ? data.slots : [];
  } catch (error) {
    logger.error('[PluginSettingsModal] Failed to load theme slots', error);
    availableSlots.value = [];
  } finally {
    loadingSlots.value = false;
  }
}

watch(
  () => props.plugin,
  (plugin) => {
    selectedSlots.value = readSlotsFromSettings(plugin?.settings);
  },
  { immediate: true },
);

async function save() {
  if (!props.plugin?.id) return;
  saving.value = true;
  try {
    const theme_blocks = selectedSlots.value.map((slot) => ({ slot }));
    const settings = {
      ...(props.plugin.settings ?? {}),
      theme_blocks,
    };
    await api.put(systemPaths.pluginSettings(props.plugin.id), { settings });
    toast.success.action(t('infra.plugins.themeBlocks.saved'));
    emit('saved');
    emit('close');
  } catch (error) {
    logger.error('[PluginSettingsModal] Save failed', error);
    toast.error.default(t('infra.plugins.themeBlocks.saveFailed'));
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  void loadSlots();
});
</script>
