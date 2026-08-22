<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent class="!p-0 !gap-0 max-w-2xl flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-2xl [&>button[aria-label=Close]]:hidden">
      <!-- Header -->
      <div class="h-12 px-5 bg-muted/40 border-b border-border/40 flex items-center justify-between select-none shrink-0">
        <DialogTitle class="text-sm font-bold text-foreground flex items-center gap-2">
          <ShieldCheck class="w-4 h-4 text-emerald-500" />
          <span>Security Inspector & Raw Message Headers</span>
        </DialogTitle>

        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
          @click="$emit('close')"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Security Details & Raw Headers -->
      <div class="p-5 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
        <p class="text-[11px] text-muted-foreground bg-muted/40 border border-border/40 rounded-lg px-3 py-2">
          Message authentication (SPF/DKIM/DMARC) and TLS are handled by your
          <strong class="font-semibold text-foreground">mail server / DNS (MTA)</strong>,
          not by JA-Mail. This panel only shows local message metadata.
        </p>

        <!-- Local metadata (not MTA verification) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
          <div class="p-3 rounded-xl border border-border/60 bg-muted/20 space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-foreground">From domain</span>
            </div>
            <p class="text-[10px] text-muted-foreground truncate">{{ senderDomain }}</p>
          </div>

          <div class="p-3 rounded-xl border border-border/60 bg-muted/20 space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-foreground">Sender</span>
            </div>
            <p class="text-[10px] text-muted-foreground truncate">{{ message?.sender.email }}</p>
          </div>

          <div class="p-3 rounded-xl border border-border/60 bg-muted/20 space-y-1">
            <div class="flex items-center justify-between">
              <span class="text-[11px] font-bold text-foreground">Auth (MTA)</span>
            </div>
            <p class="text-[10px] text-muted-foreground truncate">Configure SPF/DKIM/DMARC on DNS</p>
          </div>
        </div>

        <!-- Technical Metadata -->
        <div class="p-3 rounded-xl bg-muted/20 border border-border/40 space-y-2 text-xs">
          <div class="flex justify-between py-0.5 border-b border-border/20">
            <span class="text-muted-foreground font-medium">Message-ID:</span>
            <span class="font-mono text-[11px] text-foreground">&lt;{{ message?.id }}@{{ senderDomain }}&gt;</span>
          </div>
          <div class="flex justify-between py-0.5 border-b border-border/20">
            <span class="text-muted-foreground font-medium">Stored:</span>
            <span class="font-mono text-[11px] text-muted-foreground">Local mailbox index</span>
          </div>
          <div class="flex justify-between py-0.5">
            <span class="text-muted-foreground font-medium">Delivery Time:</span>
            <span class="text-[11px] text-foreground">{{ message?.date }}</span>
          </div>
        </div>

        <!-- Local field summary (not live MIME) -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
              <FileCode class="w-3.5 h-3.5" />
              <span>Local field summary</span>
            </label>
            <Button
              variant="outline"
              size="sm"
              class="h-6 text-[10px] gap-1 px-2 shadow-xs"
              @click="copyHeaders"
            >
              <Copy class="w-3 h-3" />
              <span>Copy Headers</span>
            </Button>
          </div>

          <pre class="p-3 rounded-xl bg-muted/50 border border-border/60 text-[10px] font-mono text-muted-foreground overflow-x-auto select-all leading-relaxed whitespace-pre-wrap">{{ rawHeaders }}</pre>
        </div>
      </div>

      <!-- Footer -->
      <div class="h-11 px-5 bg-muted/30 border-t border-border/40 flex items-center justify-end shrink-0">
        <Button
          size="sm"
          class="h-7 text-xs px-3"
          @click="$emit('close')"
        >
          Close
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import {
  ShieldCheck,
  X,
  Copy,
  FileCode,
} from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
} from '@/shared/components/ui';
import type { MailMessage } from '@/modules/Mail/composables/useMailClient';

const props = defineProps<{
    isOpen: boolean;
    message: MailMessage | null;
}>();

defineEmits<{
    (e: 'close'): void;
}>();

const toast = useToast();

const senderDomain = computed(() => {
    if (!props.message?.sender.email) return 'domain.com';
    const parts = props.message.sender.email.split('@');
    return parts[1] || 'domain.com';
});

const rawHeaders = computed(() => {
    if (!props.message) return '';
    return `From: "${props.message.sender.name}" <${props.message.sender.email}>
To: <${props.message.recipients.join(', ')}>
Subject: ${props.message.subject}
Message-ID: <${props.message.id}@${senderDomain.value}>
Date: ${props.message.date}
X-Jejakawan-Store: local-mailbox-index
Note: SPF/DKIM/DMARC are configured on your mail server DNS, not by JA-Mail.`;
});

const copyHeaders = async () => {
    try {
        await navigator.clipboard.writeText(rawHeaders.value);
        toast.success.action('Message summary copied to clipboard');
    } catch {
        toast.error.action('Failed to copy');
    }
};
</script>
