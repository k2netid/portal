<template>
  <div class="h-full flex flex-col bg-card overflow-hidden">
    <!-- Top Action Toolbar -->
    <div
      v-if="message"
      class="h-12 px-4 border-b border-border/40 flex items-center justify-between gap-2 shrink-0 bg-background/50 backdrop-blur-sm"
    >
      <div class="flex items-center gap-1.5 flex-wrap">
        <!-- Mobile Back Button -->
        <Button
          variant="ghost"
          size="icon"
          class="md:hidden h-8 w-8 text-muted-foreground"
          @click="$emit('back')"
        >
          <ArrowLeft class="w-4 h-4" />
        </Button>

        <Button
          variant="outline"
          size="sm"
          class="h-7 gap-1.5 text-xs px-2.5 shadow-xs"
          @click="$emit('reply', message)"
        >
          <Reply class="w-3.5 h-3.5" />
          <span>{{ $t('mail.reply') }}</span>
        </Button>

        <Button
          variant="outline"
          size="sm"
          class="h-7 gap-1.5 text-xs px-2.5 hidden sm:inline-flex shadow-xs"
          @click="$emit('forward', message)"
        >
          <Forward class="w-3.5 h-3.5" />
          <span>{{ $t('mail.forward') }}</span>
        </Button>

        <Button
          v-if="message.folder === 'scheduled'"
          variant="outline"
          size="sm"
          class="h-7 gap-1.5 text-xs px-2.5 shadow-xs text-amber-700 dark:text-amber-400 border-amber-500/30"
          :title="$t('mail.cancel_schedule')"
          @click="$emit('cancel-schedule', message.id)"
        >
          <Clock class="w-3.5 h-3.5" />
          <span>{{ $t('mail.cancel_schedule') }}</span>
        </Button>

        <!-- Move To Folder Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              class="h-7 gap-1 text-xs px-2 text-muted-foreground hover:text-foreground shadow-xs"
              :title="$t('mail.move')"
            >
              <FolderInput class="w-3.5 h-3.5" />
              <span class="hidden xl:inline">{{ $t('mail.move') }}</span>
              <ChevronDown class="w-3 h-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="start" class="w-44 text-xs shadow-2xl">
            <DropdownMenuItem
              v-for="target in ['inbox', 'archive', 'spam', 'trash', 'drafts']"
              :key="target"
              :disabled="message.folder === target"
              class="gap-2 cursor-pointer text-xs"
              @click="$emit('move-to-folder', message.id, target)"
            >
              <Folder class="w-3.5 h-3.5 text-muted-foreground" />
              <span>{{ $t(`mail.folder_${target}`) }}</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Labels Tagging Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              class="h-7 gap-1 text-xs px-2 text-muted-foreground hover:text-foreground shadow-xs"
              :title="$t('mail.labels')"
            >
              <Tag class="w-3.5 h-3.5" />
              <span class="hidden xl:inline">{{ $t('mail.labels') }}</span>
              <ChevronDown class="w-3 h-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="start" class="w-48 text-xs shadow-2xl">
            <DropdownMenuItem
              v-for="lbl in availableLabels"
              :key="lbl.id"
              class="gap-2 cursor-pointer text-xs justify-between"
              @click="$emit('toggle-label', message.id, lbl.id)"
            >
              <div class="flex items-center gap-2">
                <span :class="['w-2.5 h-2.5 rounded-full', lbl.color]" />
                <span>{{ lbl.name }}</span>
              </div>
              <Check v-if="message.labels && message.labels.includes(lbl.id)" class="w-3.5 h-3.5 text-primary" />
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Snooze Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              class="h-7 gap-1 text-xs px-2 text-muted-foreground hover:text-foreground shadow-xs"
              :title="$t('mail.snooze')"
            >
              <Clock class="w-3.5 h-3.5 text-amber-500" />
              <span class="hidden xl:inline">{{ $t('mail.snooze') }}</span>
              <ChevronDown class="w-3 h-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="start" class="w-56 text-xs p-1.5 shadow-2xl">
            <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('mail.snooze') }}
            </div>
            <DropdownMenuItem class="gap-2 cursor-pointer text-xs" @click="handleSnooze('later_today')">
              <Clock class="w-3.5 h-3.5 text-muted-foreground" />
              <span>{{ $t('mail.snooze_later_today') }}</span>
            </DropdownMenuItem>
            <DropdownMenuItem class="gap-2 cursor-pointer text-xs" @click="handleSnooze('tomorrow')">
              <Clock class="w-3.5 h-3.5 text-muted-foreground" />
              <span>{{ $t('mail.snooze_tomorrow') }}</span>
            </DropdownMenuItem>
            <DropdownMenuItem class="gap-2 cursor-pointer text-xs" @click="handleSnooze('next_week')">
              <Clock class="w-3.5 h-3.5 text-muted-foreground" />
              <span>{{ $t('mail.snooze_next_week') }}</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Trash / Restore / Delete Actions -->
        <Button
          v-if="message.folder !== 'trash'"
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-destructive transition-colors rounded-lg"
          :title="$t('mail.trash')"
          @click="$emit('move-to-trash', message.id)"
        >
          <Trash2 class="w-3.5 h-3.5" />
        </Button>

        <template v-else>
          <Button
            variant="outline"
            size="sm"
            class="h-7 gap-1.5 text-xs text-primary px-2.5"
            @click="$emit('restore-from-trash', message.id)"
          >
            <RotateCcw class="w-3.5 h-3.5" />
            <span>{{ $t('mail.restore') }}</span>
          </Button>

          <Button
            variant="destructive"
            size="sm"
            class="h-7 gap-1.5 text-xs px-2.5 shadow-xs"
            @click="$emit('delete-permanently', message.id)"
          >
            <Trash2 class="w-3.5 h-3.5" />
            <span>{{ $t('mail.delete_permanently') }}</span>
          </Button>
        </template>
      </div>

      <!-- Right Header Actions -->
      <div class="flex items-center gap-1">
        <!-- Thread Expand / Collapse All (if multiple messages in thread) -->
        <template v-if="allThreadMessages.length > 1">
          <Button
            variant="ghost"
            size="sm"
            class="h-7 text-[10px] text-muted-foreground hover:text-foreground px-2"
            @click="toggleAllThreadCards"
          >
            {{ areAllExpanded ? $t('mail.collapse_all') : $t('mail.expand_all') }}
          </Button>
        </template>

        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-amber-500 rounded-lg"
          @click="$emit('toggle-star', message.id)"
        >
          <Star
            :class="[
              'w-3.5 h-3.5',
              message.isStarred ? 'text-amber-500 fill-amber-500' : ''
            ]"
          />
        </Button>
        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
          :title="$t('mail.print')"
          @click="printEmail"
        >
          <Printer class="w-3.5 h-3.5" />
        </Button>
      </div>
    </div>

    <!-- Message Content Scroll Container -->
    <div
      v-if="message"
      class="flex-1 overflow-y-auto p-4 md:p-5 space-y-4 custom-scrollbar min-h-0"
    >
      <!-- Subject & Threading Badge -->
      <div>
        <div class="flex items-center gap-2 flex-wrap">
          <h2 class="text-base md:text-lg font-bold text-foreground tracking-tight">
            {{ message.subject }}
          </h2>
          <span
            v-if="allThreadMessages.length > 1"
            class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-bold border border-primary/20"
          >
            {{ $t('mail.thread_messages', { count: allThreadMessages.length }) }}
          </span>
        </div>

        <!-- Labels List -->
        <div v-if="message.labels && message.labels.length > 0" class="flex items-center gap-1.5 mt-1.5 flex-wrap">
          <span
            v-for="labelId in message.labels"
            :key="labelId"
            class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20"
          >
            {{ labelId }}
          </span>
        </div>
      </div>

      <!-- Historical Conversation Thread Cards (Previous Messages in Thread) -->
      <div v-if="previousThreadMessages.length > 0" class="space-y-2 border-b border-border/40 pb-4">
        <div
          v-for="tMsg in previousThreadMessages"
          :key="tMsg.id"
          class="rounded-xl border border-border/50 bg-card overflow-hidden transition-all shadow-xs"
        >
          <!-- Collapsed Summary Row -->
          <div
            class="p-2.5 px-3.5 flex items-center justify-between cursor-pointer hover:bg-muted/30 select-none transition-colors"
            @click="toggleThreadCard(tMsg.id)"
          >
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
              <div class="w-6 h-6 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-[10px] border border-primary/20 shrink-0">
                {{ tMsg.sender.name.charAt(0) }}
              </div>
              <span class="text-xs font-semibold text-foreground truncate max-w-[130px]">{{ tMsg.sender.name }}</span>
              <span class="text-[11px] text-muted-foreground truncate flex-1 min-w-0">{{ tMsg.snippet }}</span>
            </div>
            <div class="flex items-center gap-2 shrink-0 ml-2">
              <span class="text-[10px] text-muted-foreground">{{ tMsg.date }}</span>
              <ChevronDown :class="['w-3.5 h-3.5 text-muted-foreground transition-transform', isThreadCardExpanded(tMsg.id) ? 'rotate-180' : '']" />
            </div>
          </div>

          <!-- Expanded Body for Thread Item -->
          <div v-show="isThreadCardExpanded(tMsg.id)" class="p-3.5 border-t border-border/30 bg-muted/10 space-y-3 animate-in fade-in-50 duration-150">
            <div class="text-[11px] text-muted-foreground flex items-center justify-between">
              <span>From: <strong class="text-foreground">{{ tMsg.sender.name }}</strong> &lt;{{ tMsg.sender.email }}&gt;</span>
              <span>To: {{ tMsg.recipients.join(', ') }}</span>
            </div>
            <!-- eslint-disable vue/no-v-html -- sanitized via DOMPurify -->
            <div
              class="prose prose-sm dark:prose-invert max-w-none text-foreground/90 leading-relaxed text-xs overflow-x-auto"
              v-html="sanitizeBody(tMsg.body)"
            />
            <!-- eslint-enable vue/no-v-html -->
          </div>
        </div>
      </div>

      <!-- Current / Main Message Sender Info Card -->
      <div class="flex items-start justify-between gap-3 p-3 rounded-xl bg-muted/30 border border-border/40">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-xs border border-primary/20 shrink-0">
            {{ message.sender.name.charAt(0) }}
          </div>
          <div>
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="font-semibold text-xs text-foreground">{{ message.sender.name }}</span>
              <span class="text-[11px] text-muted-foreground">&lt;{{ message.sender.email }}&gt;</span>

              <!-- RFC 8058 One-Click Unsubscribe Action -->
              <Button
                v-if="hasUnsubscribe"
                variant="outline"
                size="sm"
                class="h-5 text-[10px] gap-1 px-1.5 border-red-500/30 text-red-600 dark:text-red-400 hover:bg-red-500/10 font-semibold rounded-md shadow-none"
                @click="isUnsubscribeModalOpen = true"
              >
                <ExternalLink class="w-2.5 h-2.5" />
                <span>{{ $t('mail.unsubscribe') }}</span>
              </Button>
            </div>
            <p class="text-[11px] text-muted-foreground mt-0.5">
              to: <span class="font-medium text-foreground/80">{{ message.recipients.join(', ') }}</span>
            </p>
          </div>
        </div>

        <div class="text-right shrink-0 space-y-1">
          <span class="text-[11px] text-muted-foreground block">{{ message.date }}</span>
          <button
            type="button"
            class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-semibold transition-colors cursor-pointer"
            title="Local message security info (auth is configured on your mail server / DNS)"
            @click="isSecurityModalOpen = true"
          >
            <ShieldCheck class="w-3 h-3" />
            <span>Security info</span>
          </button>
        </div>
      </div>

      <!-- Security / Remote Images Privacy Shield Banner -->
      <div
        v-if="hasRemoteImages && !showRemoteImages"
        class="flex items-center justify-between p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-800 dark:text-amber-300"
      >
        <div class="flex items-center gap-2">
          <ShieldAlert class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" />
          <span class="text-[11px] leading-tight">
            Remote images are blocked to protect your privacy and prevent tracking.
          </span>
        </div>
        <Button
          variant="outline"
          size="sm"
          class="h-6 text-[10px] font-semibold border-amber-500/30 hover:bg-amber-500/20 text-amber-900 dark:text-amber-200 shrink-0 ml-2"
          @click="showRemoteImages = true"
        >
          Load Images
        </Button>
      </div>

      <!-- Attachments Section -->
      <div
        v-if="message.attachments && message.attachments.length > 0"
        class="space-y-2 pt-1"
      >
        <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
          <Paperclip class="w-3 h-3 text-primary" />
          {{ $t('mail.attachments') }} ({{ message.attachments.length }})
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
          <div
            v-for="att in message.attachments"
            :key="att.id"
            class="p-2.5 rounded-xl border border-border/60 bg-muted/20 hover:bg-muted/40 transition-colors flex items-center justify-between gap-2 group"
          >
            <div class="flex items-center gap-2 min-w-0">
              <FileIcon class="w-4 h-4 text-primary shrink-0" />
              <div class="min-w-0">
                <p class="text-xs font-medium text-foreground truncate">{{ att.name }}</p>
                <p class="text-[10px] text-muted-foreground">{{ att.size }}</p>
              </div>
            </div>
            <Button
              variant="ghost"
              size="icon"
              class="h-6 w-6 text-muted-foreground hover:text-foreground"
              :title="att.url ? $t('mail.download') : 'Download not available for local-only attachments'"
              :disabled="!att.url"
              @click="downloadAttachment(att)"
            >
              <Download class="w-3 h-3" />
            </Button>
          </div>
        </div>
      </div>

      <!-- Sanitized Body Content (Protected against XSS with DOMPurify) -->
      <div class="prose prose-sm dark:prose-invert max-w-none pt-3 border-t border-border/40 text-foreground/90 leading-relaxed text-xs overflow-x-auto">
        <!-- eslint-disable vue/no-v-html -- sanitized via DOMPurify -->
        <div v-html="sanitizedHtmlBody" />
        <!-- eslint-enable vue/no-v-html -->
      </div>

      <!-- Quick Reply Section -->
      <div class="pt-4 border-t border-border/40 space-y-2">
        <div class="flex items-center justify-between">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
            <Reply class="w-3 h-3 text-primary" />
            {{ $t('mail.quick_reply') }}
            <span class="text-[9px] font-normal normal-case text-muted-foreground">(opens composer)</span>
          </h4>
          <Button
            variant="ghost"
            size="sm"
            class="h-6 text-[11px] gap-1 text-muted-foreground px-2 cursor-not-allowed opacity-60"
            disabled
            title="AI draft reply is not wired in kernel yet"
          >
            <Sparkles class="w-3 h-3 text-amber-500" />
            <span>AI Draft Reply (preview)</span>
          </Button>
        </div>
        <div class="space-y-2">
          <Textarea
            v-model="quickReplyText"
            :rows="2"
            :placeholder="$t('mail.quick_reply_placeholder', { name: message.sender.name })"
            class="w-full text-xs rounded-xl bg-muted/20 border-border/60 focus-visible:bg-background"
          />
          <div class="flex justify-end gap-2">
            <Button
              size="sm"
              class="gap-1.5 text-xs h-7 px-3"
              :disabled="!quickReplyText.trim()"
              @click="sendQuickReply"
            >
              <Send class="w-3 h-3" />
              <span>Open Reply Composer</span>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty Detail State -->
    <div
      v-else
      class="h-full flex flex-col items-center justify-center p-8 text-center text-muted-foreground space-y-3"
    >
      <div class="w-12 h-12 rounded-2xl bg-muted/30 border border-border/40 flex items-center justify-center">
        <Mail class="w-6 h-6 text-muted-foreground/60 stroke-[1.5]" />
      </div>
      <div>
        <h3 class="text-sm font-bold text-foreground">{{ $t('mail.select_message') }}</h3>
        <p class="text-xs text-muted-foreground/70 max-w-sm mt-1">
          {{ $t('mail.select_message_desc') }}
        </p>
      </div>
    </div>

    <!-- Security & Headers Modal -->
    <MailSecurityModal
      :is-open="isSecurityModalOpen"
      :message="message"
      @close="isSecurityModalOpen = false"
    />

    <!-- RFC 8058 One-Click Unsubscribe Confirmation Modal -->
    <Dialog :open="isUnsubscribeModalOpen" @update:open="v => isUnsubscribeModalOpen = v">
      <DialogContent class="!p-5 !gap-4 max-w-md bg-card border border-border/80 shadow-2xl rounded-2xl z-[1200] [&>button[aria-label=Close]]:hidden">
        <DialogTitle class="text-sm font-bold text-foreground flex items-center gap-2">
          <AlertTriangle class="w-4 h-4 text-amber-500" />
          <span>{{ $t('mail.unsubscribe_confirm_title') }}</span>
        </DialogTitle>
        <p class="text-xs text-muted-foreground leading-relaxed">
          {{ $t('mail.unsubscribe_confirm_desc') }}
        </p>
        <div class="flex items-center justify-end gap-2 pt-2">
          <Button
            variant="ghost"
            size="sm"
            class="h-8 text-xs"
            @click="isUnsubscribeModalOpen = false"
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            size="sm"
            class="h-8 text-xs font-semibold"
            @click="confirmUnsubscribe"
          >
            {{ $t('mail.unsubscribe') }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import DOMPurify from 'dompurify';
import { useToast } from '@/shared/composables/useToast';
import {
  Reply,
  Forward,
  Star,
  Trash2,
  RotateCcw,
  Printer,
  Paperclip,
  File as FileIcon,
  Download,
  Send,
  Sparkles,
  ArrowLeft,
  Mail,
  FolderInput,
  Folder,
  Tag,
  ChevronDown,
  Check,
  ShieldCheck,
  ShieldAlert,
  Clock,
  ExternalLink,
  AlertTriangle,
} from 'lucide-vue-next';
import {
  Button,
  Textarea,
  Dialog,
  DialogContent,
  DialogTitle,
} from '@/shared/components/ui';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/shared/components/ui/dropdown-menu';
import MailSecurityModal from '@/modules/Mail/components/mail/MailSecurityModal.vue';
import { type MailMessage, type MailAttachment, type MailLabel, normalizeSubject } from '@/modules/Mail/composables/useMailClient';

const props = withDefaults(
    defineProps<{
        message: MailMessage | null;
        allMessages?: MailMessage[];
        availableLabels?: MailLabel[];
        blockRemoteImages?: boolean;
    }>(),
    {
        allMessages: () => [],
        availableLabels: () => [],
        blockRemoteImages: true,
    }
);

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'reply', message: MailMessage, initialBody?: string): void;
    (e: 'forward', message: MailMessage): void;
    (e: 'toggle-star', id: string): void;
    (e: 'move-to-trash', id: string): void;
    (e: 'restore-from-trash', id: string): void;
    (e: 'delete-permanently', id: string): void;
    (e: 'move-to-folder', id: string, folder: string): void;
    (e: 'toggle-label', id: string, labelId: string): void;
    (e: 'snooze', id: string, snoozeUntil: string): void;
    (e: 'cancel-schedule', id: string): void;
    (e: 'send-reply', replyText: string): void;
}>();

const toast = useToast();
const quickReplyText = ref('');
const showRemoteImages = ref(!props.blockRemoteImages);
const isSecurityModalOpen = ref(false);
const isUnsubscribeModalOpen = ref(false);
const expandedThreadCardIds = ref<Set<string>>(new Set());

watch(
    () => [props.message?.id, props.blockRemoteImages] as const,
    () => {
        showRemoteImages.value = !props.blockRemoteImages;
    },
);

// Conversation Threading Calculations
const allThreadMessages = computed<MailMessage[]>(() => {
    if (!props.message) return [];
    const norm = normalizeSubject(props.message.subject);
    if (!norm) return [props.message];
    return props.allMessages.filter(m => normalizeSubject(m.subject) === norm);
});

const previousThreadMessages = computed<MailMessage[]>(() => {
    if (!props.message) return [];
    return allThreadMessages.value.filter(m => m.id !== props.message?.id);
});

const areAllExpanded = computed(() => {
    return previousThreadMessages.value.every(m => expandedThreadCardIds.value.has(m.id));
});

const toggleThreadCard = (id: string) => {
    if (expandedThreadCardIds.value.has(id)) {
        expandedThreadCardIds.value.delete(id);
    } else {
        expandedThreadCardIds.value.add(id);
    }
};

const isThreadCardExpanded = (id: string) => {
    return expandedThreadCardIds.value.has(id);
};

const toggleAllThreadCards = () => {
    if (areAllExpanded.value) {
        expandedThreadCardIds.value.clear();
    } else {
        previousThreadMessages.value.forEach(m => expandedThreadCardIds.value.add(m.id));
    }
};

// Heuristic only — kernel has no List-Unsubscribe header pipeline.
const hasUnsubscribe = computed(() => {
    if (!props.message) return false;
    const body = props.message.body || '';
    return /https?:\/\/[^\s"'<>]*unsubscribe/i.test(body);
});

const confirmUnsubscribe = () => {
    isUnsubscribeModalOpen.value = false;
    toast.warning(
        'Unsubscribe',
        'JA-Mail does not auto-unsubscribe. Use the sender’s unsubscribe link in the message body.',
    );
};

const handleSnooze = (preset: 'later_today' | 'tomorrow' | 'next_week') => {
    if (!props.message) return;
    const target = new Date();
    if (preset === 'later_today') {
        target.setHours(target.getHours() + 4);
    } else if (preset === 'tomorrow') {
        target.setDate(target.getDate() + 1);
        target.setHours(8, 0, 0, 0);
    } else if (preset === 'next_week') {
        const day = target.getDay();
        const diff = day === 0 ? 1 : 8 - day;
        target.setDate(target.getDate() + diff);
        target.setHours(8, 0, 0, 0);
    }
    emit('snooze', props.message.id, target.toISOString());
};

const hasRemoteImages = computed(() => {
    if (!props.message?.body) return false;
    return /<img[^>]+src=["'](https?:|\/\/)/i.test(props.message.body);
});

const sanitizeBody = (rawBody: string): string => {
    return DOMPurify.sanitize(rawBody || '', {
        USE_PROFILES: { html: true },
        FORBID_TAGS: ['script', 'iframe', 'object', 'embed', 'form', 'input', 'textarea'],
        FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover', 'onfocus', 'onblur'],
    });
};

const sanitizedHtmlBody = computed(() => {
    if (!props.message?.body) return '';
    let raw = props.message.body;

    if (hasRemoteImages.value && !showRemoteImages.value) {
        raw = raw.replace(/<img([^>]+)src=["'](https?:|\/\/)[^"']+["']([^>]*)>/gi, '<img$1src="data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'40\'><rect width=\'100%\' height=\'100%\' fill=\'%23f3f4f6\'/><text x=\'50%\' y=\'50%\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'%239ca3af\' font-size=\'10\'>[Image Blocked]</text></svg>"$3>');
    }

    return sanitizeBody(raw);
});

const sendQuickReply = () => {
    if (!quickReplyText.value.trim() || !props.message) return;
    emit('reply', props.message, quickReplyText.value);
    quickReplyText.value = '';
};

const downloadAttachment = (att: MailAttachment) => {
    if (att.url) {
        window.open(att.url, '_blank', 'noopener,noreferrer');
        return;
    }
    toast.info('Download unavailable', 'Attachment storage is not wired in kernel webmail yet.');
};

const printEmail = () => {
    window.print();
};
</script>
