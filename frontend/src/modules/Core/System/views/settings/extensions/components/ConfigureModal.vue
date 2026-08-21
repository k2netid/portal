<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="console-dialog-md sm:max-w-lg bg-card border border-border/80 rounded-xl">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Settings class="w-5 h-5 text-indigo-500" />
          {{ t('system.appStore.configTitle') }} — {{ activeExtConfig?.name }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          {{ t('system.appStore.configDesc') }}
        </DialogDescription>
      </DialogHeader>

      <div class="mt-4 space-y-4">
        <div class="space-y-1">
          <label class="text-xs text-muted-foreground uppercase font-mono">{{ t('system.appStore.configureModal.settingsSchema') }}</label>
          <p class="text-xs text-muted-foreground mb-4">{{ t('system.appStore.configDesc') }}</p>
          <textarea
            :value="rawSettingsJson"
            @input="$emit('update:rawSettingsJson', ($event.target as HTMLTextAreaElement).value)"
            rows="6"
            class="w-full bg-background border border-border rounded-lg p-3 font-mono text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
            :placeholder="t('common.placeholders.jsonObject')"
          />
        </div>
      </div>

      <DialogFooter class="mt-6 flex items-center justify-end gap-2">
        <Button
          variant="secondary"
          @click="$emit('update:open', false)"
        >
          {{ t('system.appStore.cancel') }}
        </Button>
        <Button
          class="bg-indigo-600 hover:bg-indigo-700 text-white border-0"
          @click="$emit('save')"
        >
          {{ t('system.appStore.saveBtn') }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, Button } from '@/shared/components/ui';
import {
  Settings,
} from 'lucide-vue-next';

interface FeatureItem {
  id: string;
  extension_slug: string;
  slug: string;
  name: string;
  description?: string;
  category: string;
  is_active: boolean;
}

interface ExtensionItem {
  id: string;
  slug: string;
  type: 'module' | 'plugin';
  name: string;
  version: string;
  status: 'active' | 'inactive';
  is_core: boolean;
  author?: string;
  license?: string;
  settings?: Record<string, unknown>;
  features?: FeatureItem[];
}

const { t } = useI18n();

defineProps<{
  open: boolean;
  activeExtConfig: ExtensionItem | null;
  rawSettingsJson: string;
}>();

defineEmits<{
  (e: 'update:open', val: boolean): void;
  (e: 'update:rawSettingsJson', val: string): void;
  (e: 'save'): void;
}>();
</script>
