<template>
  <div class="h-full flex flex-col border-r border-border/40 bg-card">
    <!-- Header: Search & Filter Tabs -->
    <div class="p-3 border-b border-border/40 space-y-2.5 shrink-0 bg-background/50 backdrop-blur-sm">
      <div class="relative">
        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground" />
        <Input
          :model-value="searchQuery"
          type="text"
          :placeholder="$t('system.mail.search_placeholder')"
          class="pl-9 h-9 text-xs bg-muted/40 border-border/60 rounded-lg focus-visible:bg-background transition-colors"
          @update:model-value="$emit('update:search-query', String($event || ''))"
        />
      </div>

      <!-- Filter Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 custom-scrollbar">
        <button
          v-for="tab in filterTabs"
          :key="tab.id"
          :class="[
            'px-2.5 py-1 rounded-md text-[11px] font-medium transition-colors shrink-0',
            filterType === tab.id
              ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
              : 'bg-muted/60 text-muted-foreground hover:bg-muted hover:text-foreground'
          ]"
          @click="$emit('update:filter-type', tab.id)"
        >
          {{ $t(`system.mail.filter_${tab.id}`) }}
        </button>
      </div>
    </div>

    <!-- Message List Container -->
    <div class="flex-1 overflow-y-auto divide-y divide-border/30 custom-scrollbar">
      <div
        v-if="messages.length === 0"
        class="h-64 flex flex-col items-center justify-center p-6 text-center text-muted-foreground space-y-2"
      >
        <Mail class="w-10 h-10 opacity-30 stroke-[1.5]" />
        <p class="text-sm font-medium">{{ $t('system.mail.no_messages') }}</p>
        <p class="text-xs text-muted-foreground/70">{{ $t('system.mail.no_messages_desc') }}</p>
      </div>

      <div
        v-for="msg in messages"
        :key="msg.id"
        :class="[
          'p-3.5 cursor-pointer transition-all relative group text-left select-none',
          selectedMessageId === msg.id
            ? 'bg-primary/10 border-l-4 border-l-primary'
            : !msg.isRead
              ? 'bg-card hover:bg-muted/40 font-semibold'
              : 'bg-card/60 opacity-85 hover:opacity-100 hover:bg-muted/30'
        ]"
        @click="$emit('select-message', msg.id)"
      >
        <div class="flex items-start justify-between gap-2">
          <!-- Sender & Unread Dot -->
          <div class="flex items-center gap-2 min-w-0">
            <span
              v-if="!msg.isRead"
              class="w-2 h-2 rounded-full bg-primary shrink-0 animate-pulse"
            />
            <span
              :class="[
                'text-xs truncate',
                !msg.isRead ? 'font-bold text-foreground' : 'font-medium text-foreground/80'
              ]"
            >
              {{ msg.sender.name }}
            </span>
          </div>

          <!-- Date & Star Action -->
          <div class="flex items-center gap-1.5 shrink-0">
            <span class="text-[10px] text-muted-foreground/80">{{ msg.date }}</span>
            <button
              type="button"
              class="text-muted-foreground/50 hover:text-amber-500 transition-colors p-0.5 rounded"
              @click.stop="$emit('toggle-star', msg.id)"
            >
              <Star
                :class="[
                  'w-3.5 h-3.5',
                  msg.isStarred ? 'text-amber-500 fill-amber-500' : ''
                ]"
              />
            </button>
          </div>
        </div>

        <!-- Subject -->
        <h4
          :class="[
            'text-xs mt-1 truncate',
            !msg.isRead ? 'font-bold text-foreground' : 'font-medium text-foreground/90'
          ]"
        >
          {{ msg.subject }}
        </h4>

        <!-- Snippet Preview -->
        <p class="text-[11px] text-muted-foreground mt-0.5 line-clamp-2 leading-relaxed">
          {{ msg.snippet }}
        </p>

        <!-- Footer: Attachments & Labels -->
        <div class="flex items-center gap-1.5 mt-2 flex-wrap">
          <span
            v-if="msg.attachments && msg.attachments.length > 0"
            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-muted text-[10px] font-medium text-muted-foreground"
          >
            <Paperclip class="w-3 h-3" />
            <span>{{ msg.attachments.length }}</span>
          </span>

          <span
            v-for="labelId in msg.labels"
            :key="labelId"
            class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-accent/20 text-accent-foreground"
          >
            {{ labelId }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  Search,
  Mail,
  Star,
  Paperclip,
} from 'lucide-vue-next';
import { Input } from '@/shared/components/ui';
import type { MailMessage } from '@/modules/Core/System/composables/useMailClient';

defineProps<{
    messages: MailMessage[];
    selectedMessageId: string | null;
    searchQuery: string;
    filterType: 'all' | 'unread' | 'starred' | 'attachments';
}>();

defineEmits<{
    (e: 'select-message', id: string): void;
    (e: 'toggle-star', id: string): void;
    (e: 'update:search-query', query: string): void;
    (e: 'update:filter-type', filter: 'all' | 'unread' | 'starred' | 'attachments'): void;
}>();

const filterTabs: { id: 'all' | 'unread' | 'starred' | 'attachments' }[] = [
    { id: 'all' },
    { id: 'unread' },
    { id: 'starred' },
    { id: 'attachments' },
];
</script>
