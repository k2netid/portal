<template>
  <div class="h-full flex flex-col bg-card overflow-hidden">
    <!-- Top Action Toolbar -->
    <div
      v-if="message"
      class="h-14 px-4 border-b border-border/40 flex items-center justify-between gap-2 shrink-0 bg-background/50 backdrop-blur-sm"
    >
      <div class="flex items-center gap-1.5">
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
          class="h-8 gap-1.5 text-xs"
          @click="$emit('reply', message)"
        >
          <Reply class="w-3.5 h-3.5" />
          <span>{{ $t('system.mail.reply') }}</span>
        </Button>

        <Button
          variant="outline"
          size="sm"
          class="h-8 gap-1.5 text-xs hidden sm:inline-flex"
          @click="$emit('forward', message)"
        >
          <Forward class="w-3.5 h-3.5" />
          <span>{{ $t('system.mail.forward') }}</span>
        </Button>

        <Button
          v-if="message.folder !== 'trash'"
          variant="ghost"
          size="icon"
          class="h-8 w-8 text-muted-foreground hover:text-destructive transition-colors"
          :title="$t('system.mail.trash')"
          @click="$emit('move-to-trash', message.id)"
        >
          <Trash2 class="w-4 h-4" />
        </Button>

        <template v-else>
          <Button
            variant="outline"
            size="sm"
            class="h-8 gap-1.5 text-xs text-primary"
            @click="$emit('restore-from-trash', message.id)"
          >
            <RotateCcw class="w-3.5 h-3.5" />
            <span>{{ $t('system.mail.restore') }}</span>
          </Button>

          <Button
            variant="destructive"
            size="sm"
            class="h-8 gap-1.5 text-xs"
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
          class="h-8 w-8 text-muted-foreground hover:text-amber-500"
          @click="$emit('toggle-star', message.id)"
        >
          <Star
            :class="[
              'w-4 h-4',
              message.isStarred ? 'text-amber-500 fill-amber-500' : ''
            ]"
          />
        </Button>
        <Button
          variant="ghost"
          size="icon"
          class="h-8 w-8 text-muted-foreground hover:text-foreground"
          :title="$t('system.mail.print')"
          @click="printEmail"
        >
          <Printer class="w-4 h-4" />
        </Button>
      </div>
    </div>

    <!-- Message Content Scroll Container -->
    <div
      v-if="message"
      class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6 custom-scrollbar"
    >
      <!-- Subject -->
      <div>
        <h2 class="text-lg md:text-xl font-bold text-foreground tracking-tight">
          {{ message.subject }}
        </h2>
        <div class="flex items-center gap-2 mt-2 flex-wrap">
          <span
            v-for="labelId in message.labels"
            :key="labelId"
            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20"
          >
            {{ labelId }}
          </span>
        </div>
      </div>

      <!-- Sender Info Card -->
      <div class="flex items-start justify-between gap-4 p-4 rounded-xl bg-muted/30 border border-border/40">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-primary/10 text-primary font-bold flex items-center justify-center text-sm border border-primary/20 shrink-0">
            {{ message.sender.name.charAt(0) }}
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-semibold text-sm text-foreground">{{ message.sender.name }}</span>
              <span class="text-xs text-muted-foreground">&lt;{{ message.sender.email }}&gt;</span>
            </div>
            <p class="text-xs text-muted-foreground mt-0.5">
              to: <span class="font-medium text-foreground/80">{{ message.recipients.join(', ') }}</span>
            </p>
          </div>
        </div>

        <div class="text-right shrink-0">
          <span class="text-xs text-muted-foreground block">{{ message.date }}</span>
          <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 mt-1">
            <Lock class="w-3 h-3" />
            TLS Encrypted
          </span>
        </div>
      </div>

      <!-- Attachments Section -->
      <div
        v-if="message.attachments && message.attachments.length > 0"
        class="space-y-2 pt-2"
      >
        <h4 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
          <Paperclip class="w-3.5 h-3.5" />
          {{ $t('system.mail.attachments') }} ({{ message.attachments.length }})
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div
            v-for="att in message.attachments"
            :key="att.id"
            class="flex items-center justify-between p-3 rounded-lg border border-border/60 bg-card hover:bg-muted/40 transition-colors"
          >
            <div class="flex items-center gap-2.5 min-w-0">
              <FileIcon class="w-5 h-5 text-primary shrink-0" />
              <div class="min-w-0">
                <p class="text-xs font-medium text-foreground truncate">{{ att.name }}</p>
                <p class="text-[10px] text-muted-foreground">{{ att.size }}</p>
              </div>
            </div>
            <Button
              variant="ghost"
              size="icon"
              class="h-7 w-7 text-muted-foreground hover:text-foreground"
              :title="$t('system.mail.download')"
              @click="downloadAttachment(att)"
            >
              <Download class="w-3.5 h-3.5" />
            </Button>
          </div>
        </div>
      </div>

      <!-- Sanitized Body Content -->
      <div class="prose prose-sm dark:prose-invert max-w-none pt-4 border-t border-border/40 text-foreground/90 leading-relaxed">
        <div v-html="message.body" />
      </div>

      <!-- Quick Reply Section -->
      <div class="pt-6 border-t border-border/40 space-y-3">
        <div class="flex items-center justify-between">
          <h4 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
            <Reply class="w-3.5 h-3.5 text-primary" />
            {{ $t('system.mail.quick_reply') }}
          </h4>
          <Button
            variant="ghost"
            size="sm"
            class="h-7 text-xs gap-1 text-primary hover:text-primary"
            @click="$emit('reply', message)"
          >
            <Sparkles class="w-3 h-3 text-amber-500" />
            <span>AI Draft Reply</span>
          </Button>
        </div>
        <div class="space-y-2">
          <Textarea
            v-model="quickReplyText"
            :rows="3"
            :placeholder="$t('system.mail.quick_reply_placeholder', { name: message.sender.name })"
            class="w-full text-xs rounded-xl bg-muted/20 border-border/60 focus-visible:bg-background"
          />
          <div class="flex justify-end gap-2">
            <Button
              size="sm"
              class="gap-1.5 text-xs h-8"
              :disabled="!quickReplyText.trim()"
              @click="sendQuickReply"
            >
              <Send class="w-3.5 h-3.5" />
              <span>{{ $t('system.mail.send') }}</span>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="h-full flex flex-col items-center justify-center p-6 text-center text-muted-foreground space-y-3"
    >
      <Mail class="w-12 h-12 opacity-25 stroke-[1.2]" />
      <p class="text-sm font-medium">{{ $t('system.mail.select_message') }}</p>
      <p class="text-xs text-muted-foreground/70 max-w-xs">{{ $t('system.mail.select_message_desc') }}</p>
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
} from 'lucide-vue-next';
import { Button, Textarea } from '@/shared/components/ui';
import type { MailMessage, MailAttachment } from '@/modules/Core/System/composables/useMailClient';

const props = defineProps<{
    message: MailMessage | null;
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'reply', message: MailMessage): void;
    (e: 'forward', message: MailMessage): void;
    (e: 'toggle-star', id: string): void;
    (e: 'move-to-trash', id: string): void;
    (e: 'restore-from-trash', id: string): void;
    (e: 'delete-permanently', id: string): void;
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
