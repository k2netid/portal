<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent
      :class="[
        '!p-0 !gap-0 flex flex-col overflow-hidden bg-card border border-border/80 shadow-2xl transition-all duration-200 rounded-2xl [&>button[aria-label=Close]]:hidden',
        isMaximized ? 'max-w-[96vw] h-[92vh]' : 'max-w-4xl h-[640px] max-h-[90vh]'
      ]"
    >
      <!-- Header -->
      <div class="h-10 px-4 bg-muted/40 border-b border-border/40 flex items-center justify-between select-none shrink-0">
        <DialogTitle class="text-xs font-bold text-foreground flex items-center gap-2">
          <Edit3 class="w-3.5 h-3.5 text-primary" />
          <span>{{ $t('system.mail.new_message') }}</span>
        </DialogTitle>

        <div class="flex items-center gap-1">
          <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
            :title="isMaximized ? 'Restore' : 'Maximize'"
            @click="isMaximized = !isMaximized"
          >
            <Maximize2 v-if="!isMaximized" class="w-3.5 h-3.5" />
            <Minimize2 v-else class="w-3.5 h-3.5" />
          </Button>
          <Button
            variant="ghost"
            size="icon"
            class="h-7 w-7 text-muted-foreground hover:text-destructive rounded-lg"
            :title="$t('system.mail.discard')"
            @click="$emit('close')"
          >
            <X class="w-4 h-4" />
          </Button>
        </div>
      </div>

      <!-- Composer Body Form -->
      <div class="flex-1 flex flex-col min-h-0 p-3 space-y-2 overflow-hidden">
        <!-- To field -->
        <div class="flex items-center border-b border-border/40 pb-1.5 gap-2 shrink-0">
          <label class="w-14 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
            {{ $t('system.mail.to') }}:
          </label>
          <Input
            :model-value="composerData.to"
            type="email"
            placeholder="recipient@example.com"
            class="border-none shadow-none focus-visible:ring-0 h-7 text-xs p-0 bg-transparent flex-1"
            @update:model-value="v => composerData.to = String(v || '')"
          />
          <div class="flex items-center gap-2 text-xs">
            <button
              type="button"
              class="text-muted-foreground hover:text-foreground text-[10px] font-semibold px-1.5 py-0.5 rounded hover:bg-muted"
              @click="showCc = !showCc"
            >
              Cc
            </button>
            <button
              type="button"
              class="text-muted-foreground hover:text-foreground text-[10px] font-semibold px-1.5 py-0.5 rounded hover:bg-muted"
              @click="showBcc = !showBcc"
            >
              Bcc
            </button>
          </div>
        </div>

        <!-- Optional Cc -->
        <div v-if="showCc" class="flex items-center border-b border-border/40 pb-1.5 gap-2 shrink-0">
          <label class="w-14 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Cc:</label>
          <Input
            :model-value="composerData.cc"
            type="email"
            placeholder="cc@example.com"
            class="border-none shadow-none focus-visible:ring-0 h-7 text-xs p-0 bg-transparent flex-1"
            @update:model-value="v => composerData.cc = String(v || '')"
          />
        </div>

        <!-- Optional Bcc -->
        <div v-if="showBcc" class="flex items-center border-b border-border/40 pb-1.5 gap-2 shrink-0">
          <label class="w-14 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Bcc:</label>
          <Input
            :model-value="composerData.bcc"
            type="email"
            placeholder="bcc@example.com"
            class="border-none shadow-none focus-visible:ring-0 h-7 text-xs p-0 bg-transparent flex-1"
            @update:model-value="v => composerData.bcc = String(v || '')"
          />
        </div>

        <!-- Subject Field -->
        <div class="flex items-center border-b border-border/40 pb-1.5 gap-2 shrink-0">
          <label class="w-14 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
            {{ $t('system.mail.subject') }}:
          </label>
          <Input
            :model-value="composerData.subject"
            type="text"
            :placeholder="$t('system.mail.subject_placeholder')"
            class="border-none shadow-none focus-visible:ring-0 h-7 text-xs p-0 bg-transparent flex-1 font-semibold text-foreground"
            @update:model-value="v => composerData.subject = String(v || '')"
          />
        </div>

        <!-- AI Assistant & Canned Templates Action Bar -->
        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-muted/20 border border-border/60 shrink-0 gap-2">
          <!-- Canned Templates Dropdown -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button
                variant="outline"
                size="sm"
                class="h-6 text-[10px] gap-1 px-2 border-border/60 text-muted-foreground hover:text-foreground shadow-xs"
              >
                <Bookmark class="w-3 h-3 text-primary" />
                <span>Templates</span>
                <ChevronDown class="w-2.5 h-2.5 opacity-60" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" class="!z-[1000] w-72 text-xs max-h-80 overflow-y-auto custom-scrollbar p-1.5 shadow-2xl">
              <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                Canned Response Templates
              </div>
              <DropdownMenuItem
                v-for="tpl in activeTemplates"
                :key="tpl.id || tpl.title"
                class="flex flex-col items-start gap-0.5 py-1.5 px-2 rounded-lg cursor-pointer hover:bg-primary/10"
                @click="insertTemplate(tpl)"
              >
                <span class="font-semibold text-xs text-foreground">{{ tpl.title }}</span>
                <span class="text-[10px] text-muted-foreground line-clamp-1">{{ tpl.snippet }}</span>
              </DropdownMenuItem>

              <DropdownMenuSeparator class="my-1" />

              <DropdownMenuItem
                class="flex items-center gap-1.5 py-1.5 px-2 text-xs text-primary font-semibold cursor-pointer rounded-lg hover:bg-primary/10"
                @click="$emit('manage-templates')"
              >
                <Settings class="w-3.5 h-3.5" />
                <span>Manage & Add Templates</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <!-- AI Assistant Tools -->
          <div class="flex items-center gap-1.5">
            <div class="hidden sm:flex items-center gap-1 text-[11px] text-primary font-semibold mr-1">
              <Sparkles class="w-3 h-3 text-amber-500" />
              <span>AI Assist:</span>
            </div>

            <Button
              variant="outline"
              size="sm"
              class="h-6 text-[10px] gap-1 px-2 border-primary/20 hover:bg-primary/10 text-primary"
              :disabled="generatingAi"
              @click="generateWithAi('Write a professional, clear, and polite email draft')"
            >
              <Loader2 v-if="generatingAi" class="w-2.5 h-2.5 animate-spin" />
              <span>Draft</span>
            </Button>
            <Button
              variant="outline"
              size="sm"
              class="h-6 text-[10px] gap-1 px-2 border-primary/20 hover:bg-primary/10 text-primary"
              :disabled="generatingAi || !composerData.body"
              @click="generateWithAi('Refine and polish this email text with professional business tone')"
            >
              <span>Polish Tone</span>
            </Button>
          </div>
        </div>

        <!-- Message Body Input with Tiptap Rich-Text Editor -->
        <div class="flex-1 flex flex-col min-h-0 pt-1 overflow-y-auto custom-scrollbar">
          <TiptapEditor
            v-model="composerData.body"
            :compact="true"
            :placeholder="$t('system.mail.body_placeholder')"
            class="flex-1 border-border/40 rounded-xl"
          />
        </div>

        <!-- Attachments List -->
        <div v-if="uploadedFiles.length > 0" class="flex flex-wrap gap-2 pt-1 shrink-0">
          <div
            v-for="(file, idx) in uploadedFiles"
            :key="idx"
            class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-muted text-[11px] border border-border/60"
          >
            <Paperclip class="w-3 h-3 text-muted-foreground" />
            <span class="truncate max-w-[140px] font-medium">{{ file.name }}</span>
            <button
              type="button"
              class="text-muted-foreground hover:text-destructive p-0.5"
              @click="removeFile(idx)"
            >
              <X class="w-3 h-3" />
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="h-12 px-4 bg-muted/30 border-t border-border/40 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-2">
          <!-- Hidden File Input -->
          <input
            ref="fileInputRef"
            type="file"
            multiple
            class="hidden"
            @change="handleFileUpload"
          >
          <Button
            variant="outline"
            size="sm"
            class="h-7 gap-1.5 text-xs text-muted-foreground hover:text-foreground px-2.5"
            @click="triggerFileInput"
          >
            <Paperclip class="w-3.5 h-3.5" />
            <span>{{ $t('system.mail.attach') }}</span>
          </Button>
        </div>

        <div class="flex items-center gap-2">
          <Button
            variant="ghost"
            size="sm"
            class="h-7 text-xs text-muted-foreground hover:text-destructive px-2.5"
            @click="$emit('close')"
          >
            {{ $t('system.mail.discard') }}
          </Button>

          <Button
            size="sm"
            class="h-7 gap-1.5 text-xs font-semibold px-3 shadow-xs"
            @click="$emit('send')"
          >
            <Send class="w-3 h-3" />
            <span>{{ $t('system.mail.send') }}</span>
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';
import {
  Edit3,
  X,
  Maximize2,
  Minimize2,
  Paperclip,
  Send,
  Sparkles,
  Loader2,
  Bookmark,
  ChevronDown,
  Settings,
} from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
  Input,
} from '@/shared/components/ui';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/shared/components/ui/dropdown-menu';
import TiptapEditor from '@/shared/components/editor/TiptapEditor.vue';
import { computed } from 'vue';
import type { MailTemplate } from '@/modules/Core/System/composables/useMailClient';

const props = defineProps<{
    isOpen: boolean;
    composerData: {
        to: string;
        cc: string;
        bcc: string;
        subject: string;
        body: string;
        attachments: File[];
    };
    templates?: MailTemplate[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'send'): void;
    (e: 'manage-templates'): void;
}>();

const cannedTemplates = [
    {
        id: 'tpl_meeting',
        title: 'Meeting Confirmation',
        snippet: 'Hi, confirming our meeting scheduled for...',
        body: 'Hi,\n\nThis is to confirm our meeting scheduled for [Date & Time]. Please let me know if you need to adjust the schedule or add additional attendees.\n\nLooking forward to speaking with you.\n\nBest regards,',
    },
    {
        id: 'tpl_ack',
        title: 'General Acknowledgment',
        snippet: 'Thank you for reaching out. We have received...',
        body: 'Hi,\n\nThank you for reaching out. We have received your message and our team is currently reviewing it. We will get back to you with an update shortly.\n\nBest regards,',
    },
    {
        id: 'tpl_quote',
        title: 'Price Quotation & Proposal',
        snippet: 'Please find attached our formal quotation...',
        body: 'Dear Client,\n\nThank you for your interest in our services. Please find attached our formal quotation and project scope for your review.\n\nFeel free to reach out if you have any questions.\n\nBest regards,',
    },
    {
        id: 'tpl_support',
        title: 'Technical Support Inquiry',
        snippet: 'Could you please provide account details...',
        body: 'Hello,\n\nThank you for contacting technical support. To help us resolve this swiftly, could you please provide your account email and a screenshot/log of the issue?\n\nThank you for your patience.\n\nBest regards,',
    },
    {
        id: 'tpl_followup',
        title: 'Follow-up Check-in',
        snippet: 'Quick follow-up on my previous message...',
        body: 'Hi,\n\nI wanted to quickly follow up on my previous message regarding [Subject]. Please let me know if you need any additional information from our side.\n\nBest regards,',
    },
];

const activeTemplates = computed(() => {
    return props.templates && props.templates.length > 0 ? props.templates : cannedTemplates;
});

const insertTemplate = (tpl: { title: string; body: string }) => {
    if (props.composerData.body.trim()) {
        props.composerData.body += `\n\n${tpl.body}`;
    } else {
        props.composerData.body = tpl.body;
    }
    toast.success.action(`Inserted "${tpl.title}" template`);
};

const toast = useToast();
const isMaximized = ref(false);
const showCc = ref(false);
const showBcc = ref(false);
const generatingAi = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);
const uploadedFiles = ref<File[]>([]);

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        for (let i = 0; i < target.files.length; i++) {
            const f = target.files[i];
            if (f) {
                uploadedFiles.value.push(f);
            }
        }
    }
};

const removeFile = (index: number) => {
    uploadedFiles.value.splice(index, 1);
};

const generateWithAi = async (instruction: string) => {
    generatingAi.value = true;
    try {
        const response = await api.post('/manage/ai/generate', {
            prompt: instruction,
            context: props.composerData.body ? `Current Content:\n${props.composerData.body}` : `Subject: ${props.composerData.subject || 'General inquiry'}`,
        });
        const content = response.data?.content || response.data?.data?.content;
        if (content) {
            props.composerData.body = content;
            toast.success.action('AI draft generated successfully!');
        }
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        generatingAi.value = false;
    }
};
</script>
