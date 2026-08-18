<template>
  <div>
    <div @click="toggleOpen">
      <slot name="trigger" />
    </div>

    <Teleport to="body">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[100] flex items-center justify-center pointer-events-none"
      >
        <div
          ref="popoverRef"
          class="bg-background border border-border shadow-2xl rounded-xl w-[480px] overflow-hidden pointer-events-auto flex flex-col transition-opacity duration-200"
          :style="windowStyle"
        >
          <div
            class="p-4 border-b border-border/50 bg-muted/20 flex items-center justify-between cursor-move select-none"
            @mousedown="startDrag"
          >
            <div class="flex items-center gap-2 font-medium text-foreground">
              <Sparkles class="w-4 h-4 text-indigo-500 fill-indigo-500/20" />
              <span>{{ t('ai.assist.title') }}</span>
            </div>
            <button
              class="text-muted-foreground hover:text-foreground transition-colors p-1 rounded-md hover:bg-muted"
              @click="close"
            >
              <X class="w-4 h-4" />
            </button>
          </div>

          <div class="p-4 border-b border-border/50 bg-card/50 grid grid-cols-2 gap-3">
            <Select
              v-model="selectedProvider"
              @update:model-value="fetchModels"
            >
              <SelectTrigger class="h-9">
                <SelectValue :placeholder="t('ai.assist.selectProvider')" />
              </SelectTrigger>
              <SelectContent class="z-[200]">
                <SelectItem
                  v-for="p in activeProviders"
                  :key="p.id"
                  :value="p.id"
                >
                  <div class="flex items-center gap-2">
                    <img
                      v-if="p.logo"
                      :src="p.logo"
                      class="w-4 h-4 object-contain"
                    >
                    {{ p.name }}
                  </div>
                </SelectItem>
              </SelectContent>
            </Select>

            <Select
              v-model="selectedModel"
              :disabled="loadingModels"
            >
              <SelectTrigger class="h-9">
                <SelectValue :placeholder="loadingModels ? t('ai.assist.loadingModels') : t('ai.assist.selectModel')" />
              </SelectTrigger>
              <SelectContent class="z-[200] max-h-[250px]">
                <SelectItem
                  v-for="m in currentModels"
                  :key="m.id"
                  :value="m.id"
                >
                  {{ m.name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="p-4 space-y-4 bg-background">
            <div class="grid grid-cols-2 gap-3">
              <Button
                variant="outline"
                class="justify-start gap-2.5 h-auto py-2.5 px-3 hover:bg-primary/5 hover:text-primary hover:border-primary/20 transition-colors group"
                @click="handleCommand(t('ai.assist.commands.fixGrammar'))"
              >
                <CheckCircle2 class="w-4 h-4 text-muted-foreground group-hover:text-indigo-500" />
                <span class="font-normal truncate">{{ t('ai.assist.presets.fixGrammar') }}</span>
              </Button>
              <Button
                variant="outline"
                class="justify-start gap-2.5 h-auto py-2.5 px-3 hover:bg-info/5 hover:text-info hover:border-info/20 transition-colors group"
                @click="handleCommand(t('ai.assist.commands.rewrite'))"
              >
                <RefreshCw class="w-4 h-4 text-muted-foreground group-hover:text-blue-500" />
                <span class="font-normal truncate">{{ t('ai.assist.presets.rewrite') }}</span>
              </Button>
              <Button
                variant="outline"
                class="justify-start gap-2.5 h-auto py-2.5 px-3 hover:bg-warning/5 hover:text-warning hover:border-warning/20 transition-colors group"
                @click="handleCommand(t('ai.assist.commands.summarize'))"
              >
                <FileText class="w-4 h-4 text-muted-foreground group-hover:text-orange-500" />
                <span class="font-normal truncate">{{ t('ai.assist.presets.summarize') }}</span>
              </Button>
              <Button
                variant="outline"
                class="justify-start gap-2.5 h-auto py-2.5 px-3 hover:bg-primary/5 hover:text-primary hover:border-primary/20 transition-colors group"
                @click="handleCommand(t('ai.assist.commands.expand'))"
              >
                <Maximize2 class="w-4 h-4 text-muted-foreground group-hover:text-purple-500" />
                <span class="font-normal truncate">{{ t('ai.assist.presets.expand') }}</span>
              </Button>
            </div>

            <div class="relative py-2">
              <div class="absolute inset-0 flex items-center">
                <span class="w-full border-t border-border/60" />
              </div>
              <div class="relative flex justify-center text-[10px] font-medium tracking-wider uppercase text-muted-foreground">
                <span class="bg-background px-2">{{ t('ai.assist.customCommand') }}</span>
              </div>
            </div>

            <div class="space-y-3">
              <Textarea
                v-model="customPrompt"
                :placeholder="t('ai.assist.customPlaceholder')"
                class="min-h-[80px] text-sm resize-none focus-visible:ring-offset-0 focus-visible:ring-1"
                @keydown.enter.exact.prevent="handleCommand(customPrompt)"
              />
              <Button
                class="w-full h-10 shadow-sm"
                :disabled="!customPrompt || loading"
                @click="handleCommand(customPrompt)"
              >
                <span
                  v-if="!loading"
                  class="flex items-center gap-2 font-medium"
                >
                  {{ t('ai.assist.generateWith', { provider: selectedProviderName }) }}
                  <ArrowRight class="w-4 h-4" />
                </span>
                <span
                  v-else
                  class="flex items-center gap-2"
                >
                  <Loader2 class="w-4 h-4 animate-spin" />
                  <span>{{ t('ai.assist.generating') }}</span>
                </span>
              </Button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed, nextTick } from 'vue';
import { useI18n } from 'vue-i18n';
import {
  ArrowRight,
  CheckCircle2,
  FileText,
  Loader2,
  Maximize2,
  RefreshCw,
  Sparkles,
  X,
} from 'lucide-vue-next';
import {
    Button,
    Textarea,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/shared/components/ui';
import { AiService } from '@/modules/Intelligence/Ai/services/aiService';
import { useToast } from '@/shared/composables/useToast';
import { useSystemStore } from '@/modules/Core/System/stores/system';

const { t } = useI18n();

interface AiProvider {
    id: string;
    name: string;
    logo?: string;
}

interface AiModel {
    id: string;
    name: string;
}

const props = defineProps<{
    context?: string;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (e: 'result', content: string): void;
}>();

const toast = useToast();
const systemStore = useSystemStore();

const isOpen = ref(false);
const customPrompt = ref('');
const loading = ref(false);

const providers = ref<AiProvider[]>([]);
const selectedProvider = ref('gemini');
const selectedModel = ref('');
const models = ref<Record<string, AiModel[]>>({});
const loadingModels = ref(false);

const activeProviders = computed(() => {
    return providers.value.filter((p) => {
        const key = (systemStore.settings as Record<string, unknown>)?.[`${p.id}_api_key`];
        return !!key;
    });
});

const currentModels = computed(() => models.value[selectedProvider.value] || []);

const selectedProviderName = computed(() => {
    return providers.value.find((p) => p.id === selectedProvider.value)?.name || selectedProvider.value;
});

const popoverRef = ref<HTMLElement | null>(null);
const position = ref({ x: 0, y: 0 });
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });

const windowStyle = computed(() => ({
    position: 'absolute' as const,
    top: `${position.value.y}px`,
    left: `${position.value.x}px`,
    margin: 0,
}));

const toggleOpen = async () => {
    if (props.disabled) return;

    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        await fetchProviders();
        await nextTick();
        centerPopover();
    }
};

const close = () => {
    isOpen.value = false;
};

const centerPopover = () => {
    if (popoverRef.value) {
        const { width, height } = popoverRef.value.getBoundingClientRect();
        position.value = {
            x: (window.innerWidth - width) / 2,
            y: (window.innerHeight - height) / 2,
        };
    }
};

const startDrag = (e: MouseEvent) => {
    isDragging.value = true;
    if (!popoverRef.value) return;
    const rect = popoverRef.value.getBoundingClientRect();
    dragOffset.value = {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top,
    };

    window.addEventListener('mousemove', onDrag);
    window.addEventListener('mouseup', stopDrag);
};

const onDrag = (e: MouseEvent) => {
    if (!isDragging.value) return;
    position.value = {
        x: e.clientX - dragOffset.value.x,
        y: e.clientY - dragOffset.value.y,
    };
};

const stopDrag = () => {
    isDragging.value = false;
    window.removeEventListener('mousemove', onDrag);
    window.removeEventListener('mouseup', stopDrag);
};

const fetchProviders = async () => {
    try {
        const response = await AiService.providers();
        providers.value = response.data;

        if (activeProviders.value.length === 0) {
            toast.service.warning(t('ai.assist.noProviders'));
        } else {
            const defaultProvider = systemStore.settings?.ai_default_provider as string;
            const isDefaultActive = activeProviders.value.find((p) => p.id === defaultProvider);

            if (isDefaultActive) {
                selectedProvider.value = defaultProvider;
            } else if (activeProviders.value.length > 0) {
                selectedProvider.value = activeProviders.value[0]!.id;
            }

            fetchModels();
        }
    } catch (e) {
        logger.error('Failed to init AI', e);
    }
};

const fetchModels = async () => {
    const provider = selectedProvider.value;
    const providerModels = models.value[provider];

    if (providerModels) {
        if (!selectedModel.value && providerModels.length > 0) {
            selectedModel.value = providerModels[0]!.id;
        }
        return;
    }

    loadingModels.value = true;
    try {
        const response = await AiService.models(provider);
        const resultModels = response.data;
        models.value[provider] = resultModels;

        if (resultModels && resultModels.length > 0) {
            selectedModel.value = resultModels[0]!.id;
        }
    } catch (e) {
        logger.error('Failed to load models', e);
    } finally {
        loadingModels.value = false;
    }
};

const handleCommand = async (prompt: string) => {
    if (!prompt) return;

    loading.value = true;
    try {
        const response = await AiService.generate({
            prompt,
            context: props.context,
            provider: selectedProvider.value,
            model: selectedModel.value,
        });

        if (response.data?.content) {
            emit('result', response.data.content);
            close();
            customPrompt.value = '';
        }
    } catch (error: unknown) {
        logger.error('AI Ops Error:', error);
        const err = error as import('axios').AxiosError<{ message?: string }>;
        toast.service.error(t('ai.assist.errorTitle'), err.response?.data?.message || t('ai.assist.generateFailed'));
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    /* fetch on open */
});
</script>
