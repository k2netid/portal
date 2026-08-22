<template>
  <div
    :class="[
      'h-full flex flex-col justify-between p-2.5 select-none transition-all duration-300 relative bg-card/30',
      isMinimized ? 'w-16 items-center' : 'w-60'
    ]"
  >
    <!-- Navigation Scroll Container -->
    <div class="flex-1 w-full overflow-y-auto overflow-x-hidden custom-scrollbar space-y-4 pr-1 min-h-0">
      <!-- Main Folders Section -->
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
        <div v-if="!isMinimized" class="flex items-center justify-between px-2 mb-1.5">
          <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/70">
            {{ $t('system.mail.labels') }}
          </p>
          <button
            type="button"
            class="text-muted-foreground hover:text-primary p-0.5 rounded transition-colors"
            :title="$t('system.mail.manage_labels')"
            @click.stop="$emit('manage-labels')"
          >
            <Plus class="w-3.5 h-3.5" />
          </button>
        </div>
        <button
          v-else
          type="button"
          class="w-full flex justify-center p-2 text-muted-foreground hover:text-primary"
          :title="$t('system.mail.manage_labels')"
          @click.stop="$emit('manage-labels')"
        >
          <Plus class="w-3.5 h-3.5" />
        </button>

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

    <!-- Bottom Footer: Storage Info (100% Dynamic from Backend Settings) -->
    <div class="w-full pt-2 border-t border-border/40 shrink-0">
      <div
        v-if="!isMinimized"
        class="p-2 rounded-xl bg-muted/30 border border-border/40 space-y-1.5"
        :title="storageStats ? `${storageStats.used_formatted} of ${storageStats.quota_formatted} (${storageStats.percentage}%)` : undefined"
      >
        <div class="flex items-center justify-between text-[11px] text-muted-foreground">
          <span class="flex items-center gap-1">
            <HardDrive class="w-3 h-3 text-primary" />
            {{ $t('system.mail.storage') }}
          </span>
          <span class="font-semibold text-foreground text-[10px]">
            {{ storageStats?.used_formatted || '0 B' }} / {{ storageStats?.quota_formatted || '...' }}
          </span>
        </div>
        <div class="w-full bg-border/60 h-1.5 rounded-full overflow-hidden">
          <div
            class="bg-primary h-full rounded-full transition-all duration-500"
            :style="{ width: `${Math.max(1, Math.min(100, storageStats?.percentage ?? 0))}%` }"
          />
        </div>
      </div>
      <div
        v-else
        class="flex justify-center p-1 text-muted-foreground cursor-help"
        :title="storageStats ? `${storageStats.used_formatted} / ${storageStats.quota_formatted} (${storageStats.percentage}%)` : undefined"
      >
        <HardDrive class="w-4 h-4 opacity-75 text-primary" />
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
  HardDrive,
  Plus,
} from 'lucide-vue-next';
import type { useMailClient } from '@/modules/Core/System/composables/useMailClient';

type MailClient = ReturnType<typeof useMailClient>;

defineProps<{
    isMinimized: boolean;
    activeFolder: MailClient['activeFolder']['value'];
    activeLabel: MailClient['activeLabel']['value'];
    folderCounts: MailClient['folderCounts']['value'];
    labels: MailClient['labels']['value'];
    storageStats?: MailClient['storageStats']['value'];
}>();

const emit = defineEmits<{
    (e: 'select-folder', folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'): void;
    (e: 'select-label', labelId: string): void;
    (e: 'manage-labels'): void;
}>();

const selectFolder = (folder: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam') => {
    emit('select-folder', folder);
};

const selectLabel = (labelId: string) => {
    emit('select-label', labelId);
};

const folderList: { id: 'inbox' | 'sent' | 'drafts' | 'trash' | 'spam'; icon: any }[] = [
    { id: 'inbox', icon: Inbox },
    { id: 'sent', icon: Send },
    { id: 'drafts', icon: FileText },
    { id: 'trash', icon: Trash2 },
    { id: 'spam', icon: AlertOctagon },
];
</script>
