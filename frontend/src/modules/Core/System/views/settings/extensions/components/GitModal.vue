<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="console-dialog-md sm:max-w-md bg-card border border-border/80 rounded-xl">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <GitBranch class="w-5 h-5 text-indigo-500 animate-pulse" />
          {{ t('system.appStore.gitTitle') }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          {{ t('system.appStore.gitDesc') }}
        </DialogDescription>
      </DialogHeader>

      <div class="mt-4 space-y-4 text-sm text-muted-foreground leading-relaxed">
        <p>
          {{ t('system.appStore.gitDesc') }}
        </p>
        <div class="space-y-2">
          <label class="text-xs text-foreground font-semibold">{{ t('system.appStore.repoUrl') }}</label>
          <input
            v-model="repoUrl"
            type="text"
            :placeholder="t('system.appStore.git.repoUrlPlaceholder')"
            class="w-full bg-background border border-border/80 rounded-lg px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
            :disabled="cloning"
          />
        </div>

        <div
          v-if="cloneError"
          class="bg-rose-500/10 border border-rose-500/20 p-3 rounded-lg flex gap-2 text-xs text-rose-400"
        >
          <AlertTriangle class="w-5 h-5 shrink-0 animate-bounce" />
          <span>{{ cloneError }}</span>
        </div>

        <div class="bg-indigo-500/10 border border-indigo-500/20 p-3 rounded-lg text-xs text-indigo-400 mt-2 flex items-start gap-2">
          <ShieldAlert class="w-4 h-4 shrink-0 mt-0.5" />
          <span>{{ t('system.appStore.gitNotice') }}</span>
        </div>
      </div>

      <DialogFooter class="mt-6 flex justify-end gap-2">
        <Button
          variant="secondary"
          :disabled="cloning"
          @click="$emit('update:open', false)"
        >
          {{ t('system.appStore.cancel') }}
        </Button>
        <Button
          class="bg-indigo-600 hover:bg-indigo-700 text-white border-0"
          :disabled="!repoUrl || cloning"
          @click="submitClone"
        >
          <span v-if="cloning" class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full border-2 border-indigo-200 border-t-indigo-600 animate-spin"></span>
            {{ t('system.appStore.gitCloning') }}
          </span>
          <span v-else>{{ t('system.appStore.gitCloneBtn') }}</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter, Button } from '@/shared/components/ui';
import {
  AlertTriangle,
  GitBranch,
  ShieldAlert,
} from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps<{
  open: boolean;
  cloning: boolean;
  cloneError: string;
}>();

const emit = defineEmits<{
  (e: 'update:open', val: boolean): void;
  (e: 'clone', repoUrl: string): void;
  (e: 'clear-error'): void;
}>();

const repoUrl = ref('');

watch(() => props.open, (newVal) => {
  if (!newVal) {
    repoUrl.value = '';
    emit('clear-error');
  }
});

const submitClone = () => {
  if (repoUrl.value) {
    emit('clone', repoUrl.value);
  }
};
</script>
