<template>
  <div
    :class="[
      'h-full flex flex-col justify-between p-2.5 select-none transition-all duration-300 relative',
      isMinimized ? 'w-16 items-center' : 'w-60'
    ]"
  >
    <!-- Top Section -->
    <div class="space-y-3 w-full flex flex-col flex-1 min-h-0">
      <!-- Compose Button & Collapse Toggle Header -->
      <div class="flex items-center gap-2">
        <Button
          v-if="!isMinimized"
          class="flex-1 justify-center gap-2 h-9 shadow-xs font-semibold text-xs tracking-tight rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 transition-all active:scale-[0.98]"
          @click="openComposer()"
        >
          <Edit3 class="w-3.5 h-3.5" />
          <span>{{ $t('system.mail.compose') }}</span>
        </Button>

        <Button
          v-else
          variant="default"
          size="icon"
          class="h-9 w-9 rounded-xl shadow-xs"
          :title="$t('system.mail.compose')"
          @click="openComposer()"
        >
          <Edit3 class="w-4 h-4" />
        </Button>

        <!-- Sidebar Collapse Toggle Button -->
        <Button
          variant="ghost"
          size="icon"
          class="h-8 w-8 text-muted-foreground hover:text-foreground shrink-0 rounded-lg"
          :title="isMinimized ? 'Expand Sidebar' : 'Minimize Sidebar'"
          @click="$emit('toggle-minimize')"
        >
          <PanelLeftClose v-if="!isMinimized" class="w-4 h-4" />
          <PanelLeftOpen v-else class="w-4 h-4" />
        </Button>
      </div>

      <!-- Navigation Scroll Container -->
      <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar space-y-4 pr-1">
        <!-- Main Folders -->
        <div class="space-y-0.5">
          <p
            v-if="!isMinimized"
            class="px-2 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70 mb-1.5"
          >
            {{ $t('system.mail.folders') }}
          </p>

          <button
            v-for="folder in folderList"
            :key="folder.id"
            :class="[
              'w-full flex items-center rounded-lg text-xs font-medium transition-all group relative',
              isMinimized ? 'justify-center p-2.5' : 'justify-between px-3 py-2',
              activeFolder === folder.id && !activeLabel
                ? 'bg-primary/10 text-primary font-semibold'
                : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.99]'
            ]"
            :title="isMinimized ? $t(`system.mail.folder_${folder.id}`) : undefined"
            @click="selectFolder(folder.id)"
          >
            <div class="flex items-center gap-2.5">
              <component
                :is="folder.icon"
                class="w-4 h-4 shrink-0 opacity-80 group-hover:opacity-100"
              />
              <span v-if="!isMinimized" class="truncate">{{ $t(`system.mail.folder_${folder.id}`) }}</span>
            </div>

            <!-- Badge Unread -->
            <template v-if="(folderCounts?.[folder.id] ?? 0) > 0">
              <span
                v-if="!isMinimized"
                :class="[
                  'px-1.5 py-0.5 rounded text-[10px] font-bold',
                  activeFolder === folder.id && !activeLabel
                    ? 'bg-primary text-primary-foreground'
                    : 'bg-muted text-muted-foreground'
                ]"
              >
                {{ folderCounts?.[folder.id] }}
              </span>
              <span
                v-else
                class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-primary"
              />
            </template>
          </button>
        </div>

        <!-- Labels Section -->
        <div class="space-y-0.5 pt-2 border-t border-border/40">
          <p
            v-if="!isMinimized"
            class="px-2 text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70 mb-1.5"
          >
            {{ $t('system.mail.labels') }}
          </p>

          <button
            v-for="label in labels"
            :key="label.id"
            :class="[
              'w-full flex items-center rounded-lg text-xs font-medium transition-all group',
              isMinimized ? 'justify-center p-2.5' : 'justify-between px-3 py-2',
              activeLabel === label.id
                ? 'bg-primary/10 text-primary font-semibold'
                : 'text-muted-foreground hover:bg-accent/10 hover:text-foreground active:scale-[0.99]'
            ]"
            :title="isMinimized ? label.name : undefined"
            @click="selectLabel(label.id)"
          >
            <div class="flex items-center gap-2.5">
              <span :class="['w-2.5 h-2.5 rounded-full shrink-0', label.color]" />
              <span v-if="!isMinimized" class="truncate">{{ label.name }}</span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom Footer Tools -->
    <div class="w-full pt-2 border-t border-border/40 space-y-2 mt-2">
      <!-- Sync Button -->
      <Button
        variant="ghost"
        size="sm"
        :class="[
          'text-xs text-muted-foreground hover:text-foreground transition-colors',
          isMinimized ? 'w-full justify-center p-0 h-8' : 'w-full justify-start gap-2 h-8 px-2'
        ]"
        :disabled="syncing"
        :title="$t('system.mail.sync_mailbox')"
        @click="$emit('sync')"
      >
        <RefreshCw :class="['w-3.5 h-3.5', syncing ? 'animate-spin text-primary' : '']" />
        <span v-if="!isMinimized">{{ syncing ? $t('system.mail.syncing') : $t('system.mail.sync_mailbox') }}</span>
      </Button>

      <!-- Storage Progress -->
      <div v-if="!isMinimized" class="p-2 rounded-xl bg-muted/30 border border-border/40 space-y-1.5">
        <div class="flex items-center justify-between text-[11px] text-muted-foreground">
          <span class="flex items-center gap-1">
            <HardDrive class="w-3 h-3" />
            {{ $t('system.mail.storage') }}
          </span>
          <span class="font-semibold text-foreground text-[10px]">1.8 GB / 15 GB</span>
        </div>
        <div class="w-full bg-border/60 h-1 rounded-full overflow-hidden">
          <div class="bg-primary h-full rounded-full w-[12%]" />
        </div>
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
  RefreshCw,
  PanelLeftClose,
  PanelLeftOpen,
} from 'lucide-vue-next';
import { Button } from '@/shared/components/ui';
import type { useMailClient } from '@/modules/Core/System/composables/useMailClient';

type MailClient = ReturnType<typeof useMailClient>;

defineProps<{
    isMinimized: boolean;
    activeFolder: MailClient['activeFolder']['value'];
    activeLabel: MailClient['activeLabel']['value'];
    folderCounts: MailClient['folderCounts']['value'];
    labels: MailClient['labels']['value'];
    syncing?: boolean;
}>();

const emit = defineEmits<{
    (e: 'select-folder', folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'): void;
    (e: 'select-label', labelId: string): void;
    (e: 'open-composer'): void;
    (e: 'toggle-minimize'): void;
    (e: 'sync'): void;
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
