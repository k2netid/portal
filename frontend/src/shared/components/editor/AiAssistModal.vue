<template>
  <Dialog
    :open="open"
    @update:open="$emit('update:open', $event)"
  >
    <DialogContent class="console-dialog-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Sparkles class="w-5 h-5 text-indigo-500" />
          <span>{{ t('ai.assist.title') }}</span>
        </DialogTitle>
        <DialogDescription>
          {{ t('ai.assist.description') }}
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 py-4">
        <div class="grid grid-cols-2 gap-2">
          <Button
            variant="outline"
            class="justify-start gap-2"
            @click="handleCommand(t('ai.assist.commands.fixGrammar'))"
          >
            <CheckCircle2 class="w-4 h-4 text-green-500" />
            {{ t('ai.assist.presets.fixGrammar') }}
          </Button>
          <Button
            variant="outline"
            class="justify-start gap-2"
            @click="handleCommand(t('ai.assist.commands.rewrite'))"
          >
            <RefreshCw class="w-4 h-4 text-blue-500" />
            {{ t('ai.assist.presets.rewrite') }}
          </Button>
          <Button
            variant="outline"
            class="justify-start gap-2"
            @click="handleCommand(t('ai.assist.commands.summarize'))"
          >
            <FileText class="w-4 h-4 text-orange-500" />
            {{ t('ai.assist.presets.summarize') }}
          </Button>
          <Button
            variant="outline"
            class="justify-start gap-2"
            @click="handleCommand(t('ai.assist.commands.expand'))"
          >
            <Maximize2 class="w-4 h-4 text-purple-500" />
            {{ t('ai.assist.presets.expand') }}
          </Button>
        </div>

        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <span class="w-full border-t" />
          </div>
          <div class="relative flex justify-center text-xs uppercase">
            <span class="bg-background px-2 text-muted-foreground">{{ t('ai.assist.orCustom') }}</span>
          </div>
        </div>

        <div class="flex gap-2">
          <Input
            v-model="customPrompt"
            :placeholder="t('ai.assist.customInputPlaceholder')"
            @keydown.enter="handleCommand(customPrompt)"
          />
          <Button
            :disabled="!customPrompt || loading"
            @click="handleCommand(customPrompt)"
          >
            <ArrowRight class="w-4 h-4" />
          </Button>
        </div>

        <div
          v-if="loading"
          class="flex items-center justify-center p-4 text-sm text-muted-foreground animate-pulse"
        >
          <Sparkles class="w-4 h-4 mr-2 animate-spin" />
          {{ t('ai.assist.generatingMagic') }}
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  ArrowRight,
  CheckCircle2,
  FileText,
  Maximize2,
  RefreshCw,
  Sparkles,
} from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    Button,
    Input,
} from '@/shared/components/ui';
import { AiService } from '@/shared/services/aiService';
import { useToast } from '@/shared/composables/useToast';

const { t } = useI18n();

const props = defineProps<{
    open: boolean;
    context?: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'result', content: string): void;
}>();

const toast = useToast();

const customPrompt = ref('');
const loading = ref(false);

const handleCommand = async (prompt: string) => {
    if (!prompt) return;

    loading.value = true;
    try {
        const response = await AiService.generate({
            prompt,
            context: props.context,
        });

        if (response.data?.content) {
            emit('result', response.data.content);
            emit('update:open', false);
            customPrompt.value = '';
        }
    } catch (error: unknown) {
        logger.error('AI Ops Error:', error);
        const err = error as { response?: { data?: { message?: string } } };
        toast.service.error(t('ai.assist.errorTitle'), err.response?.data?.message || t('ai.assist.generateFailed'));
    } finally {
        loading.value = false;
    }
};
</script>
