<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent
      :class="[
        '!p-0 !gap-0 flex flex-col overflow-hidden bg-card border border-border/80 shadow-2xl transition-all duration-200 rounded-2xl [&>button[aria-label=Close]]:hidden',
        isMaximized ? 'max-w-[96vw] h-[92vh]' : 'max-w-3xl h-[520px] max-h-[85vh]'
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

        <!-- AI Assistant Action Bar -->
        <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-primary/5 border border-primary/15 shrink-0">
          <div class="flex items-center gap-1.5 text-[11px] text-primary font-semibold">
            <Sparkles class="w-3.5 h-3.5 text-amber-500" />
            <span>AI Copilot Assist</span>
          </div>

          <div class="flex items-center gap-1.5">
            <Button
              variant="outline"
              size="sm"
              class="h-6 text-[10px] gap-1 px-2 border-primary/20 hover:bg-primary/10 text-primary"
              :disabled="generatingAi"
              @click="generateWithAi('Write a professional and polite email draft')"
            >
              <Loader2 v-if="generatingAi" class="w-2.5 h-2.5 animate-spin" />
              <span>Generate Draft</span>
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

        <!-- Message Body Input (Takes all remaining vertical space) -->
        <div class="flex-1 flex flex-col min-h-0 pt-1">
          <Textarea
            v-model="composerData.body"
            :placeholder="$t('system.mail.body_placeholder')"
            class="flex-1 w-full resize-none border-border/40 bg-muted/10 text-xs rounded-xl focus-visible:bg-background p-3 leading-relaxed"
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
} from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
  Input,
  Textarea,
} from '@/shared/components/ui';

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
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'send'): void;
}>();

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
