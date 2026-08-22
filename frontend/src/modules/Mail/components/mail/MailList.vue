<template>
  <div class="h-full flex flex-col border-r border-border/40 bg-card">
    <!-- Header: Search Bar (Aligned with h-12 top row, responsive left padding) -->
    <div class="h-12 pl-3 lg:pl-6 pr-3 border-b border-border/40 flex items-center gap-2 shrink-0 bg-background/50 backdrop-blur-sm">
      <div class="relative flex-1">
        <Search class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
        <Input
          :model-value="searchQuery"
          type="text"
          :placeholder="$t('system.mail.search_placeholder')"
          class="pl-8 h-8 text-xs bg-muted/40 border-border/60 rounded-lg focus-visible:bg-background transition-colors"
          @update:model-value="$emit('update:search-query', String($event || ''))"
        />
      </div>
      <Button
        variant="ghost"
        size="icon"
        class="h-8 w-8 text-muted-foreground hover:text-foreground shrink-0 rounded-lg"
        :disabled="loading"
        :title="$t('system.mail.refresh')"
        @click="$emit('refresh')"
      >
        <RefreshCw :class="['w-3.5 h-3.5', loading ? 'animate-spin text-primary' : '']" />
      </Button>
    </div>

    <!-- Sub-header Filter Pills -->
    <div class="px-3 py-1.5 border-b border-border/30 bg-muted/10 flex items-center gap-1.5 overflow-x-auto custom-scrollbar shrink-0">
      <button
        v-for="tab in filterTabs"
        :key="tab.id"
        :class="[
          'px-2 py-0.5 rounded-md text-[10px] font-medium transition-colors shrink-0',
          filterType === tab.id
            ? 'bg-primary text-primary-foreground font-semibold shadow-xs'
            : 'bg-muted/60 text-muted-foreground hover:bg-muted hover:text-foreground'
        ]"
        @click="$emit('update:filter-type', tab.id)"
      >
        {{ $t(`system.mail.filter_${tab.id}`) }}
      </button>
    </div>

    <!-- Message List Container -->
    <div class="flex-1 overflow-y-auto divide-y divide-border/30 custom-scrollbar min-h-0">
      <!-- Loading Skeleton -->
      <div v-if="loading" class="p-4 space-y-3">
        <div v-for="i in 5" :key="i" class="space-y-2 p-2 rounded-lg bg-muted/20 animate-pulse">
          <div class="flex justify-between">
            <div class="h-3 w-28 bg-muted rounded" />
            <div class="h-2 w-12 bg-muted rounded" />
          </div>
          <div class="h-3 w-44 bg-muted/80 rounded" />
          <div class="h-2 w-full bg-muted/60 rounded" />
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="messages.length === 0"
        class="h-64 flex flex-col items-center justify-center p-6 text-center text-muted-foreground space-y-2"
      >
        <Mail class="w-9 h-9 opacity-30 stroke-[1.5]" />
        <p class="text-xs font-semibold">{{ $t('system.mail.no_messages') }}</p>
        <p class="text-[11px] text-muted-foreground/70 max-w-xs">{{ $t('system.mail.no_messages_desc') }}</p>
      </div>

      <!-- Message Items -->
      <div
        v-for="msg in messages"
        :key="msg.id"
        :class="[
          'p-3 cursor-pointer transition-all relative group text-left select-none',
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
          <div class="flex items-center gap-1.5 min-w-0">
            <span
              v-if="!msg.isRead"
              class="w-1.5 h-1.5 rounded-full bg-primary shrink-0 animate-pulse"
            />
            <span
              :class="[
                'text-xs truncate',
                !msg.isRead ? 'font-bold text-foreground' : 'font-medium text-foreground/80'
              ]"
            >
              {{ msg.sender.name }}
            </span>
            <span
              v-if="getThreadCount(msg) > 1"
              class="px-1.5 py-0.5 rounded bg-muted text-[9px] font-bold text-muted-foreground shrink-0"
              :title="$t('system.mail.thread_messages', { count: getThreadCount(msg) })"
            >
              {{ getThreadCount(msg) }}
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
            'text-xs mt-0.5 truncate',
            !msg.isRead ? 'font-bold text-foreground' : 'font-medium text-foreground/90'
          ]"
        >
          {{ msg.subject }}
        </h4>

        <!-- Snippet Preview -->
        <p class="text-[11px] text-muted-foreground mt-0.5 line-clamp-2 leading-snug">
          {{ msg.snippet }}
        </p>

        <!-- Footer: Attachments & Labels -->
        <div v-if="(msg.attachments && msg.attachments.length > 0) || (msg.labels && msg.labels.length > 0)" class="flex items-center gap-1.5 mt-1.5 flex-wrap">
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

    <!-- Pagination Footer -->
    <div
      v-if="totalMessages > 0"
      class="h-10 px-3 border-t border-border/40 flex items-center justify-between shrink-0 bg-background/50 text-[11px] text-muted-foreground select-none"
    >
      <span>
        {{ fromRange }}-{{ toRange }} of {{ totalMessages }}
      </span>

      <div class="flex items-center gap-1">
        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded"
          :disabled="currentPage <= 1"
          @click="$emit('prev-page')"
        >
          <ChevronLeft class="w-3.5 h-3.5" />
        </Button>
        <span class="text-[10px] font-semibold text-foreground px-1">
          {{ currentPage }} / {{ lastPage }}
        </span>
        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded"
          :disabled="currentPage >= lastPage"
          @click="$emit('next-page')"
        >
          <ChevronRight class="w-3.5 h-3.5" />
        </Button>
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
  RefreshCw,
  ChevronLeft,
  ChevronRight,
} from 'lucide-vue-next';
import { Input, Button } from '@/shared/components/ui';
import { type MailMessage, normalizeSubject } from '@/modules/Mail/composables/useMailClient';

const props = withDefaults(
    defineProps<{
        messages: MailMessage[];
        selectedMessageId: string | null;
        searchQuery: string;
        filterType: 'all' | 'unread' | 'starred' | 'attachments';
        loading?: boolean;
        currentPage?: number;
        lastPage?: number;
        totalMessages?: number;
        fromRange?: number;
        toRange?: number;
    }>(),
    {
        loading: false,
        currentPage: 1,
        lastPage: 1,
        totalMessages: 0,
        fromRange: 0,
        toRange: 0,
    }
);

const getThreadCount = (msg: MailMessage): number => {
    const norm = normalizeSubject(msg.subject);
    if (!norm) return 1;
    return props.messages.filter((m) => normalizeSubject(m.subject) === norm).length;
};

defineEmits<{
    (e: 'select-message', id: string): void;
    (e: 'toggle-star', id: string): void;
    (e: 'update:search-query', query: string): void;
    (e: 'update:filter-type', filter: 'all' | 'unread' | 'starred' | 'attachments'): void;
    (e: 'refresh'): void;
    (e: 'next-page'): void;
    (e: 'prev-page'): void;
}>();

const filterTabs: { id: 'all' | 'unread' | 'starred' | 'attachments' }[] = [
    { id: 'all' },
    { id: 'unread' },
    { id: 'starred' },
    { id: 'attachments' },
];
</script>
