<template>
  <div class="h-full flex flex-col justify-between p-3 select-none">
    <div class="space-y-4">
      <!-- Compose Button -->
      <Button
        class="w-full justify-center gap-2 h-10 shadow-sm font-semibold tracking-tight rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 transition-all active:scale-[0.98]"
        @click="openComposer()"
      >
        <Edit3 class="w-4 h-4" />
        <span>{{ $t('system.mail.compose') }}</span>
      </Button>

      <!-- Main Folders -->
      <div class="space-y-1">
        <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70 mb-2">
          {{ $t('system.mail.folders') }}
        </p>

        <button
          v-for="folder in folderList"
          :key="folder.id"
          :class="[
            'w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-colors',
            activeFolder === folder.id && !activeLabel
              ? 'bg-primary/10 text-primary font-semibold'
              : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.99]'
          ]"
          @click="selectFolder(folder.id)"
        >
          <div class="flex items-center gap-2.5">
            <component
              :is="folder.icon"
              class="w-4 h-4 shrink-0 opacity-80"
            />
            <span class="truncate">{{ $t(`system.mail.folder_${folder.id}`) }}</span>
          </div>

          <span
            v-if="folderCounts[folder.id] && folderCounts[folder.id] > 0"
            :class="[
              'px-1.5 py-0.5 rounded text-[10px] font-bold',
              activeFolder === folder.id && !activeLabel
                ? 'bg-primary text-primary-foreground'
                : 'bg-muted text-muted-foreground'
            ]"
          >
            {{ folderCounts[folder.id] }}
          </span>
        </button>
      </div>

      <!-- Labels / Categories -->
      <div class="space-y-1 pt-2 border-t border-border/40">
        <p class="px-2 text-[11px] font-bold uppercase tracking-wider text-muted-foreground/70 mb-2">
          {{ $t('system.mail.labels') }}
        </p>

        <button
          v-for="label in labels"
          :key="label.id"
          :class="[
            'w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-colors',
            activeLabel === label.id
              ? 'bg-primary/10 text-primary font-semibold'
              : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.99]'
          ]"
          @click="selectLabel(label.id)"
        >
          <div class="flex items-center gap-2.5">
            <span :class="['w-2.5 h-2.5 rounded-full shrink-0', label.color]" />
            <span class="truncate">{{ label.name }}</span>
          </div>
        </button>
      </div>
    </div>

    <!-- Storage Usage Footer -->
    <div class="p-3 rounded-xl bg-muted/40 border border-border/40 space-y-2 mt-4">
      <div class="flex items-center justify-between text-xs text-muted-foreground">
        <span class="flex items-center gap-1.5">
          <HardDrive class="w-3.5 h-3.5" />
          {{ $t('system.mail.storage') }}
        </span>
        <span class="font-semibold text-foreground">1.8 GB / 15 GB</span>
      </div>
      <div class="w-full bg-border/60 h-1.5 rounded-full overflow-hidden">
        <div class="bg-primary h-full rounded-full w-[12%]" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  Inbox,
  Send,
  FileText,
  Trash2,
  AlertOctagon,
  Edit3,
  HardDrive,
} from 'lucide-vue-next';
import { Button } from '@/shared/components/ui';
import type { useMailClient } from '@/modules/Core/System/composables/useMailClient';

type MailClient = ReturnType<typeof useMailClient>;

const props = defineProps<{
    activeFolder: MailClient['activeFolder']['value'];
    activeLabel: MailClient['activeLabel']['value'];
    folderCounts: MailClient['folderCounts']['value'];
    labels: MailClient['labels']['value'];
}>();

const emit = defineEmits<{
    (e: 'select-folder', folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'): void;
    (e: 'select-label', labelId: string): void;
    (e: 'open-composer'): void;
}>();

const selectFolder = (folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam') => {
    emit('select-folder', folder);
};

const selectLabel = (labelId: string) => {
    emit('select-label', labelId);
};

const openComposer = () => {
    emit('open-composer');
};

const folderList: { id: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'; icon: any }[] = [
    { id: 'inbox', icon: Inbox },
    { id: 'sent', icon: Send },
    { id: 'drafts', icon: FileText },
    { id: 'trash', icon: Trash2 },
    { id: 'spam', icon: AlertOctagon },
];
</script>
