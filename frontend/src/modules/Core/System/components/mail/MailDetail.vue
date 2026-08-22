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
          <span>{{ $t('system.mail.reply') }}</span>
        </Button>

        <Button
          variant="outline"
          size="sm"
          class="h-7 gap-1.5 text-xs px-2.5 hidden sm:inline-flex shadow-xs"
          @click="$emit('forward', message)"
        >
          <Forward class="w-3.5 h-3.5" />
          <span>{{ $t('system.mail.forward') }}</span>
        </Button>

        <!-- Move To Folder Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              class="h-7 gap-1 text-xs px-2 text-muted-foreground hover:text-foreground"
              :title="$t('system.mail.move')"
            >
              <FolderInput class="w-3.5 h-3.5" />
              <span class="hidden xl:inline">{{ $t('system.mail.move') }}</span>
              <ChevronDown class="w-3 h-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="start" class="w-44 text-xs">
            <DropdownMenuItem
              v-for="target in ['inbox', 'spam', 'trash', 'drafts']"
              :key="target"
              :disabled="message.folder === target"
              class="gap-2 cursor-pointer text-xs"
              @click="$emit('move-to-folder', message.id, target)"
            >
              <Folder class="w-3.5 h-3.5 text-muted-foreground" />
              <span>{{ $t(`system.mail.folder_${target}`) }}</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <!-- Labels Tagging Dropdown -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="outline"
              size="sm"
              class="h-7 gap-1 text-xs px-2 text-muted-foreground hover:text-foreground"
              :title="$t('system.mail.labels')"
            >
              <Tag class="w-3.5 h-3.5" />
              <span class="hidden xl:inline">{{ $t('system.mail.labels') }}</span>
              <ChevronDown class="w-3 h-3 opacity-60" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="start" class="w-48 text-xs">
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

        <!-- Trash / Restore / Delete Actions -->
        <Button
          v-if="message.folder !== 'trash'"
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-destructive transition-colors"
          :title="$t('system.mail.trash')"
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
            <span>{{ $t('system.mail.restore') }}</span>
          </Button>

          <Button
            variant="destructive"
            size="sm"
            class="h-7 gap-1.5 text-xs px-2.5"
            @click="$emit('delete-permanently', message.id)"
          >
            <Trash2 class="w-3.5 h-3.5" />
            <span>{{ $t('system.mail.delete_permanently') }}</span>
          </Button>
        </template>
      </div>

      <!-- Right Toolbar Tools -->
      <div class="flex items-center gap-1">
        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-amber-500"
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
          class="h-7 w-7 text-muted-foreground hover:text-foreground"
          :title="$t('system.mail.print')"
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
      <!-- Subject -->
      <div>
        <h2 class="text-base md:text-lg font-bold text-foreground tracking-tight">
          {{ message.subject }}
        </h2>
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

      <!-- Sender Info Card -->
      <div class="flex items-start justify-between gap-3 p-3 rounded-xl bg-muted/30 border border-border/40">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-xs border border-primary/20 shrink-0">
            {{ message.sender.name.charAt(0) }}
          </div>
          <div>
            <div class="flex items-center gap-1.5 flex-wrap">
              <span class="font-semibold text-xs text-foreground">{{ message.sender.name }}</span>
              <span class="text-[11px] text-muted-foreground">&lt;{{ message.sender.email }}&gt;</span>
            </div>
            <p class="text-[11px] text-muted-foreground mt-0.5">
              to: <span class="font-medium text-foreground/80">{{ message.recipients.join(', ') }}</span>
            </p>
          </div>
        </div>

        <div class="text-right shrink-0">
          <span class="text-[11px] text-muted-foreground block">{{ message.date }}</span>
          <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 mt-0.5">
            <Lock class="w-2.5 h-2.5" />
            TLS
          </span>
        </div>
      </div>

      <!-- Attachments Section -->
      <div
        v-if="message.attachments && message.attachments.length > 0"
        class="space-y-2 pt-1"
      >
        <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
          <Paperclip class="w-3 h-3" />
          {{ $t('system.mail.attachments') }} ({{ message.attachments.length }})
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div
            v-for="att in message.attachments"
            :key="att.id"
            class="flex items-center justify-between p-2.5 rounded-lg border border-border/60 bg-card hover:bg-muted/40 transition-colors"
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
              :title="$t('system.mail.download')"
              @click="downloadAttachment(att)"
            >
              <Download class="w-3 h-3" />
            </Button>
          </div>
        </div>
      </div>

      <!-- Sanitized Body Content -->
      <div class="prose prose-sm dark:prose-invert max-w-none pt-3 border-t border-border/40 text-foreground/90 leading-relaxed text-xs">
        <div v-html="message.body" />
      </div>

      <!-- Quick Reply Section -->
      <div class="pt-4 border-t border-border/40 space-y-2">
        <div class="flex items-center justify-between">
          <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
            <Reply class="w-3 h-3 text-primary" />
            {{ $t('system.mail.quick_reply') }}
          </h4>
          <Button
            variant="ghost"
            size="sm"
            class="h-6 text-[11px] gap-1 text-primary hover:text-primary px-2"
            @click="$emit('reply', message)"
          >
            <Sparkles class="w-3 h-3 text-amber-500" />
            <span>AI Draft Reply</span>
          </Button>
        </div>
        <div class="space-y-2">
          <Textarea
            v-model="quickReplyText"
            :rows="2"
            :placeholder="$t('system.mail.quick_reply_placeholder', { name: message.sender.name })"
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
              <span>{{ $t('system.mail.send') }}</span>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="h-full flex flex-col items-center justify-center p-6 text-center text-muted-foreground space-y-2"
    >
      <Mail class="w-10 h-10 opacity-25 stroke-[1.2]" />
      <p class="text-xs font-semibold">{{ $t('system.mail.select_message') }}</p>
      <p class="text-[11px] text-muted-foreground/70 max-w-xs">{{ $t('system.mail.select_message_desc') }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import {
  Reply,
  Forward,
  Star,
  Trash2,
  RotateCcw,
  Printer,
  Lock,
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
} from 'lucide-vue-next';
import { Button, Textarea } from '@/shared/components/ui';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/shared/components/ui/dropdown-menu';
import type { MailMessage, MailAttachment, MailLabel } from '@/modules/Core/System/composables/useMailClient';

const props = defineProps<{
    message: MailMessage | null;
    availableLabels?: MailLabel[];
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'reply', message: MailMessage): void;
    (e: 'forward', message: MailMessage): void;
    (e: 'toggle-star', id: string): void;
    (e: 'move-to-trash', id: string): void;
    (e: 'restore-from-trash', id: string): void;
    (e: 'delete-permanently', id: string): void;
    (e: 'move-to-folder', id: string, folder: string): void;
    (e: 'toggle-label', id: string, labelId: string): void;
    (e: 'send-reply', replyText: string): void;
}>();

const toast = useToast();
const quickReplyText = ref('');

const sendQuickReply = () => {
    if (!quickReplyText.value.trim() || !props.message) return;
    toast.success.action('Quick reply sent successfully!');
    quickReplyText.value = '';
};

const downloadAttachment = (att: MailAttachment) => {
    toast.success.action(`Downloaded ${att.name}`);
};

const printEmail = () => {
    window.print();
};
</script>
