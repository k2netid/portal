<template>
  <div>
    <!-- Main Composer Dialog -->
    <Dialog
      :open="isOpen"
      @update:open="v => { if(!v) handleCloseRequest() }"
    >
      <DialogContent
        :class="[
          '!p-0 !gap-0 flex flex-col overflow-hidden bg-card border border-border/80 shadow-2xl transition-all duration-200 rounded-2xl [&>button[aria-label=Close]]:hidden',
          isMaximized ? 'max-w-[96vw] h-[92vh]' : 'max-w-4xl h-[640px] max-h-[90vh]'
        ]"
        @pointer-down-outside.prevent="handleCloseRequest"
        @interact-outside.prevent="handleCloseRequest"
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
              @click="handleCloseRequest"
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
              @update:model-value="v => patchComposer({ to: String(v || '') })"
            />
            <div class="flex items-center gap-2 text-xs">
              <button
                type="button"
                :class="[
                  'text-[10px] font-semibold px-1.5 py-0.5 rounded transition-colors',
                  showCc ? 'bg-primary/15 text-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted'
                ]"
                @click="showCc = !showCc"
              >
                Cc
              </button>
              <button
                type="button"
                :class="[
                  'text-[10px] font-semibold px-1.5 py-0.5 rounded transition-colors',
                  showBcc ? 'bg-primary/15 text-primary' : 'text-muted-foreground hover:text-foreground hover:bg-muted'
                ]"
                @click="showBcc = !showBcc"
              >
                Bcc
              </button>
            </div>
          </div>

          <!-- Optional Cc -->
          <div v-if="showCc" class="flex items-center border-b border-border/40 pb-1.5 gap-2 shrink-0 animate-in fade-in-50 duration-150">
            <label class="w-14 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Cc:</label>
            <Input
              :model-value="composerData.cc"
              type="email"
              placeholder="cc@example.com"
              class="border-none shadow-none focus-visible:ring-0 h-7 text-xs p-0 bg-transparent flex-1"
              @update:model-value="v => patchComposer({ cc: String(v || '') })"
            />
          </div>

          <!-- Optional Bcc -->
          <div v-if="showBcc" class="flex items-center border-b border-border/40 pb-1.5 gap-2 shrink-0 animate-in fade-in-50 duration-150">
            <label class="w-14 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Bcc:</label>
            <Input
              :model-value="composerData.bcc"
              type="email"
              placeholder="bcc@example.com"
              class="border-none shadow-none focus-visible:ring-0 h-7 text-xs p-0 bg-transparent flex-1"
              @update:model-value="v => patchComposer({ bcc: String(v || '') })"
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
              @update:model-value="v => patchComposer({ subject: String(v || '') })"
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
                  <span>{{ $t('system.mail.templates_title') }}</span>
                  <ChevronDown class="w-2.5 h-2.5 opacity-60" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="start" class="!z-[1000] w-72 text-xs max-h-80 overflow-y-auto custom-scrollbar p-1.5 shadow-2xl">
                <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-muted-foreground">
                  {{ $t('system.mail.templates_title') }}
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
                  <span>{{ $t('system.mail.manage_labels') }} / Templates</span>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            <!-- AI Assistant Tools — only when global AI + mail drafting ready -->
            <div
              v-if="aiCopilotVisible"
              class="flex items-center gap-1.5"
            >
              <div class="hidden sm:flex items-center gap-1 text-[11px] text-primary font-semibold mr-1">
                <Sparkles class="w-3 h-3 text-amber-500" />
                <span>AI Copilot:</span>
              </div>

              <Button
                variant="outline"
                size="sm"
                class="h-6 text-[10px] gap-1 px-2 border-primary/20 hover:bg-primary/10 text-primary"
                :disabled="generatingAi || !aiCopilotEnabled"
                :title="aiCopilotEnabled ? 'Generate draft' : aiBlockedReason"
                @click="runDraftAi"
              >
                <Loader2 v-if="generatingAi" class="w-2.5 h-2.5 animate-spin" />
                <span>Draft</span>
              </Button>
              <Button
                variant="outline"
                size="sm"
                class="h-6 text-[10px] gap-1 px-2 border-primary/20 hover:bg-primary/10 text-primary"
                :disabled="generatingAi || !composerData.body || !aiCopilotEnabled"
                :title="aiCopilotEnabled ? 'Polish tone' : aiBlockedReason"
                @click="runPolishAi"
              >
                <span>Polish Tone</span>
              </Button>
            </div>
            <p
              v-else-if="aiBlockedReason"
              class="hidden sm:block text-[10px] text-muted-foreground max-w-[220px] truncate"
              :title="aiBlockedReason"
            >
              {{ aiBlockedReason }}
            </p>
          </div>

          <!-- Message Body Input with Tiptap Rich-Text Editor -->
          <div class="flex-1 flex flex-col min-h-0 pt-1">
            <TiptapEditor
              :model-value="composerData.body"
              :compact="true"
              :resizable="false"
              :placeholder="$t('system.mail.body_placeholder')"
              class="flex-1 flex flex-col min-h-0 border-border/40 rounded-xl overflow-hidden shadow-none"
              @update:model-value="v => patchComposer({ body: String(v || '') })"
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
            <!-- Discard button -->
            <Button
              variant="ghost"
              size="sm"
              class="h-7 text-xs text-muted-foreground hover:text-destructive px-2.5"
              @click="handleCloseRequest"
            >
              {{ $t('system.mail.discard') }}
            </Button>

            <!-- Save as Draft Button -->
            <Button
              variant="outline"
              size="sm"
              class="h-7 gap-1 text-xs px-2.5 shadow-xs"
              :disabled="!isDirty"
              @click="saveDraftAction"
            >
              <Save class="w-3 h-3" />
              <span>{{ $t('system.mail.save_draft') }}</span>
            </Button>

            <!-- Split Button: Send Email + Schedule Send Dropdown -->
            <div class="inline-flex rounded-lg shadow-xs overflow-hidden">
              <Button
                size="sm"
                class="h-7 gap-1.5 text-xs font-semibold px-3 rounded-r-none"
                :disabled="!isValidRecipient"
                @click="$emit('send')"
              >
                <Send class="w-3 h-3" />
                <span>{{ $t('system.mail.send') }}</span>
              </Button>

              <!-- Schedule Send Dropdown -->
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button
                    size="sm"
                    class="h-7 px-1.5 rounded-l-none border-l border-primary-foreground/20"
                    :disabled="!isValidRecipient"
                    title="Schedule Send"
                  >
                    <ChevronDown class="w-3 h-3" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="!z-[1000] w-64 text-xs p-1.5 shadow-2xl">
                  <div class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-1.5">
                    <Clock class="w-3 h-3 text-primary" />
                    <span>{{ $t('system.mail.schedule_send') }}</span>
                  </div>
                  <DropdownMenuItem
                    class="py-1.5 cursor-pointer text-xs"
                    @click="handleSchedule('tomorrow_morning')"
                  >
                    {{ $t('system.mail.schedule_tomorrow_morning') }}
                  </DropdownMenuItem>
                  <DropdownMenuItem
                    class="py-1.5 cursor-pointer text-xs"
                    @click="handleSchedule('tomorrow_afternoon')"
                  >
                    {{ $t('system.mail.schedule_tomorrow_afternoon') }}
                  </DropdownMenuItem>
                  <DropdownMenuItem
                    class="py-1.5 cursor-pointer text-xs"
                    @click="handleSchedule('monday_morning')"
                  >
                    {{ $t('system.mail.schedule_monday_morning') }}
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Unsaved Changes Confirmation Dialog -->
    <Dialog :open="isUnsavedConfirmOpen" @update:open="v => isUnsavedConfirmOpen = v">
      <DialogContent class="!p-5 !gap-4 max-w-md bg-card border border-border/80 shadow-2xl rounded-2xl z-[1200] [&>button[aria-label=Close]]:hidden">
        <DialogTitle class="text-sm font-bold text-foreground flex items-center gap-2">
          <AlertCircle class="w-4 h-4 text-amber-500" />
          <span>{{ $t('system.mail.unsaved_draft_title') }}</span>
        </DialogTitle>
        <p class="text-xs text-muted-foreground leading-relaxed">
          {{ $t('system.mail.unsaved_draft_desc') }}
        </p>
        <div class="flex items-center justify-end gap-2 pt-2">
          <Button
            variant="ghost"
            size="sm"
            class="h-8 text-xs"
            @click="isUnsavedConfirmOpen = false"
          >
            {{ $t('system.mail.keep_editing') }}
          </Button>
          <Button
            variant="destructive"
            size="sm"
            class="h-8 text-xs"
            @click="discardDraftAction"
          >
            {{ $t('system.mail.discard_draft') }}
          </Button>
          <Button
            size="sm"
            class="h-8 text-xs font-semibold"
            @click="saveDraftAction"
          >
            {{ $t('system.mail.save_draft') }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import { AiService } from '@/shared/services/aiService';
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
  Save,
  Clock,
  AlertCircle,
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
import type { MailTemplate } from '@/modules/Mail/composables/useMailClient';

type ComposerData = {
    to: string;
    cc: string;
    bcc: string;
    subject: string;
    body: string;
    attachments: File[];
};

type MailAiPrefs = {
    ai_ready: boolean;
    ai_enabled: boolean;
    ai_provider: string;
    ai_tone: string;
    ai_scope_drafting: boolean;
    ai_guardrail_pii_masking: boolean;
    global_ready: boolean;
};

const props = defineProps<{
    isOpen: boolean;
    composerData: ComposerData;
    templates?: MailTemplate[];
    aiPrefs?: MailAiPrefs;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'send'): void;
    (e: 'save-draft', data: ComposerData): void;
    (e: 'schedule-send', scheduledAt: string): void;
    (e: 'manage-templates'): void;
    (e: 'update:composerData', data: ComposerData): void;
}>();

const patchComposer = (patch: Partial<ComposerData>): void => {
    emit('update:composerData', { ...props.composerData, ...patch });
};

const toast = useToast();

const toneLabel = (tone: string): string => {
    const map: Record<string, string> = {
        professional: 'professional business',
        friendly: 'friendly and warm',
        concise: 'concise and direct',
        executive: 'formal executive',
    };
    return map[tone] || 'professional business';
};

const maskPii = (text: string): string => {
    return text
        .replace(/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/g, '[REDACTED_EMAIL]')
        .replace(/\b(?:\d[ -]*?){13,19}\b/g, '[REDACTED_CARD]')
        .replace(/(?:password|passwd|pwd|token|secret|api[_-]?key)\s*[:=]\s*\S+/gi, '[REDACTED_SECRET]');
};

const aiCopilotEnabled = computed(() => Boolean(props.aiPrefs?.ai_ready));
const aiCopilotVisible = computed(() => {
    // Show bar when mail AI master is on OR when we need to explain why it's blocked
    return Boolean(props.aiPrefs?.ai_enabled) || Boolean(props.aiPrefs?.global_ready);
});
const aiBlockedReason = computed(() => {
    if (!props.aiPrefs) return 'AI prefs not loaded';
    if (!props.aiPrefs.global_ready) return 'Enable Settings → AI and add a provider API key';
    if (!props.aiPrefs.ai_enabled) return 'Enable AI Copilot in Mail settings';
    if (!props.aiPrefs.ai_scope_drafting) return 'Enable drafting scope in Mail AI settings';
    if (!props.aiPrefs.ai_ready) return 'AI not ready';
    return '';
});
const isMaximized = ref(false);
const showCc = ref(false);
const showBcc = ref(false);
const generatingAi = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);
const uploadedFiles = ref<File[]>([]);
const isUnsavedConfirmOpen = ref(false);

watch(
    () => props.isOpen,
    (open) => {
        uploadedFiles.value = open ? [...(props.composerData.attachments || [])] : [];
    },
);

const isDirty = computed(() => {
    return Boolean(
        props.composerData.to.trim() ||
        props.composerData.subject.trim() ||
        (props.composerData.body && props.composerData.body !== '<p></p>' && props.composerData.body.trim()) ||
        uploadedFiles.value.length > 0
    );
});

const isValidRecipient = computed(() => {
    return Boolean(props.composerData.to.trim());
});

const handleCloseRequest = () => {
    if (isDirty.value) {
        isUnsavedConfirmOpen.value = true;
    } else {
        emit('close');
    }
};

const saveDraftAction = () => {
    emit('save-draft', { ...props.composerData });
    isUnsavedConfirmOpen.value = false;
    emit('close');
};

const discardDraftAction = () => {
    isUnsavedConfirmOpen.value = false;
    emit('close');
};

const handleSchedule = (preset: 'tomorrow_morning' | 'tomorrow_afternoon' | 'monday_morning') => {
    let target = new Date();
    if (preset === 'tomorrow_morning') {
        target.setDate(target.getDate() + 1);
        target.setHours(8, 0, 0, 0);
    } else if (preset === 'tomorrow_afternoon') {
        target.setDate(target.getDate() + 1);
        target.setHours(13, 0, 0, 0);
    } else if (preset === 'monday_morning') {
        const day = target.getDay();
        const diff = day === 0 ? 1 : 8 - day;
        target.setDate(target.getDate() + diff);
        target.setHours(8, 0, 0, 0);
    }
    emit('schedule-send', target.toISOString());
};

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
    const htmlBody = tpl.body.replace(/\n/g, '<br/>');
    const currentBody = props.composerData.body;
    const nextBody = currentBody.trim() && currentBody !== '<p></p>'
        ? `${currentBody}<br/><br/>${htmlBody}`
        : htmlBody;
    patchComposer({ body: nextBody });
    toast.success.action(`Inserted "${tpl.title}" template`);
};

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
        patchComposer({ attachments: [...uploadedFiles.value] });
    }
    if (target) {
        target.value = '';
    }
};

const removeFile = (index: number) => {
    uploadedFiles.value.splice(index, 1);
    patchComposer({ attachments: [...uploadedFiles.value] });
};

const generateWithAi = async (instruction: string) => {
    if (!aiCopilotEnabled.value) {
        toast.error.action(aiBlockedReason.value || 'AI Copilot unavailable');
        return;
    }

    generatingAi.value = true;
    try {
        const provider = props.aiPrefs?.ai_provider || undefined;
        let context = props.composerData.body
            ? `Current Content:\n${props.composerData.body}`
            : `Subject: ${props.composerData.subject || 'General inquiry'}`;
        if (props.aiPrefs?.ai_guardrail_pii_masking) {
            context = maskPii(context);
        }

        const response = await AiService.generate({
            prompt: instruction,
            context,
            provider,
        });
        const content = response.data?.content || '';
        if (content) {
            patchComposer({ body: content.replace(/\n/g, '<br/>') });
            toast.success.action('AI draft generated — review before sending');
        }
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        generatingAi.value = false;
    }
};

const runDraftAi = () => {
    const tone = toneLabel(props.aiPrefs?.ai_tone || 'professional');
    void generateWithAi(`Write a ${tone}, clear email draft. Do not invent facts not present in the context.`);
};

const runPolishAi = () => {
    const tone = toneLabel(props.aiPrefs?.ai_tone || 'professional');
    void generateWithAi(`Refine and polish this email with a ${tone} tone. Keep meaning; do not invent new claims.`);
};
</script>
