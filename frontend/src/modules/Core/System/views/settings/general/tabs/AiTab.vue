<template>
  <div class="space-y-6">
    <SettingGroup
      :title="$t('system.settings.groups.ai.title')"
      :description="$t('system.settings.groups.ai.description')"
      :icon="Sparkles"
      color="indigo"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Enable AI -->
        <SettingField
          v-model="(localFormData.ai_enabled as any)"
          field-key="ai_enabled"
          type="boolean"
          :label="$t('system.settings.groups.ai.enable_ai')"
          :description="$t('system.settings.groups.ai.enable_ai_desc')"
          :error="errors?.ai_enabled"
          @update:model-value="v => updateField('ai_enabled', v)"
        />
                
        <!-- Default Provider -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.groups.ai.default_provider') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.groups.ai.default_provider_desc') }}
          </p>
          <Select 
            :model-value="(localFormData.ai_default_provider as string) || 'gemini'" 
            :disabled="!localFormData.ai_enabled"
            @update:model-value="v => updateField('ai_default_provider', v)"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('system.settings.groups.ai.default_provider')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="provider in providers"
                :key="provider.id"
                :value="provider.id"
              >
                <div class="flex items-center gap-2">
                  <img
                    v-if="provider.logo"
                    :src="provider.logo"
                    :alt="provider.name"
                    class="w-4 h-4 object-contain rounded"
                  >
                  <Bot
                    v-else
                    class="w-4 h-4 text-muted-foreground"
                  />
                  <span>{{ provider.name }}</span>
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </SettingGroup>

    <!-- Providers Accordion -->
    <div
      class="space-y-4"
      :class="{ 'opacity-50 pointer-events-none': !formData.ai_enabled }"
    >
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-base font-semibold text-foreground">
            {{ $t('system.settings.groups.ai.providers') }}
          </h3>
          <p class="text-xs text-muted-foreground">
            {{ $t('system.settings.groups.ai.description') }}
          </p>
        </div>
      </div>
            
      <div
        v-for="provider in providers"
        :key="provider.id"
        class="border border-border/60 rounded-xl overflow-hidden bg-card text-card-foreground shadow-sm transition-all duration-200"
      >
        <button 
          type="button"
          class="w-full flex items-center justify-between p-4 hover:bg-muted/40 transition-colors text-left"
          @click="toggleProvider(provider.id)"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-muted/60 flex items-center justify-center p-2 border border-border/40 shrink-0">
              <img
                v-if="provider.logo"
                :src="provider.logo"
                :alt="provider.name"
                class="w-full h-full object-contain"
              >
              <Bot
                v-else
                class="w-5 h-5 text-muted-foreground"
              />
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-semibold text-sm text-foreground">{{ provider.name }}</span>
                <span 
                  v-if="localFormData.ai_default_provider === provider.id"
                  class="text-[10px] font-medium bg-primary/10 text-primary px-2 py-0.5 rounded-full"
                >
                  Default
                </span>
              </div>
              <span
                v-if="formData[`${provider.id}_model`]"
                class="text-xs text-muted-foreground block truncate"
              >
                {{ $t('system.settings.groups.ai.using_model', { model: formData[`${provider.id}_model`] }) }}
              </span>
              <span
                v-else-if="provider.description"
                class="text-xs text-muted-foreground block truncate"
              >
                {{ provider.description }}
              </span>
            </div>
          </div>
          <ChevronDown 
            class="w-5 h-5 text-muted-foreground transition-transform duration-200 shrink-0 ml-2" 
            :class="{ 'rotate-180': expandedProvider === provider.id }"
          />
        </button>

        <div
          v-show="expandedProvider === provider.id"
          class="p-4 pt-3 border-t border-border/40 bg-muted/10 space-y-4"
        >
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Col: API Key -->
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  {{ $t('system.settings.groups.ai.api_key') }}
                </label>
                <a
                  v-if="provider.docsUrl"
                  :href="provider.docsUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-xs text-primary hover:underline inline-flex items-center gap-1"
                >
                  {{ $t('system.settings.groups.ai.get_api_key') }}
                  <ExternalLink class="w-3 h-3" />
                </a>
              </div>
              <div class="relative">
                <Input 
                  :type="showKey[provider.id] ? 'text' : 'password'"
                  :placeholder="$t('system.settings.groups.ai.api_key_placeholder')"
                  :model-value="(localFormData[`${provider.id}_api_key`] as string) || ''"
                  class="pr-10"
                  @update:model-value="v => updateField(`${provider.id}_api_key`, v)"
                />
                <button 
                  type="button"
                  class="absolute right-0 top-0 h-full px-3 text-muted-foreground hover:text-foreground transition-colors"
                  tabindex="-1"
                  @click="toggleKeyVisibility(provider.id)"
                >
                  <EyeOff
                    v-if="showKey[provider.id]"
                    class="w-4 h-4"
                  />
                  <Eye
                    v-else
                    class="w-4 h-4"
                  />
                </button>
              </div>
              <p
                v-if="errors?.[`${provider.id}_api_key`]"
                class="text-xs text-destructive"
              >
                {{ Array.isArray(errors?.[`${provider.id}_api_key` || '']) ? (errors?.[`${provider.id}_api_key` || ''] as string[])[0] : errors?.[`${provider.id}_api_key` || ''] }}
              </p>
            </div>

            <!-- Right Col: Model Selection & Actions -->
            <div class="space-y-1.5">
              <label class="block text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                {{ $t('system.settings.groups.ai.model_select') }}
              </label>
              <div class="flex gap-2">
                <Select 
                  :model-value="(localFormData[`${provider.id}_model`] as string) || ''" 
                  :disabled="loadingModels[provider.id]"
                  @update:model-value="v => updateField(`${provider.id}_model`, v)"
                  @update:open="(open) => { if(open) fetchModels(provider.id) }"
                >
                  <SelectTrigger class="w-full">
                    <SelectValue :placeholder="loadingModels[provider.id] ? $t('system.settings.groups.ai.loading_models') : ((localFormData[`${provider.id}_model`] as string) || $t('system.settings.groups.ai.select_model'))" />
                  </SelectTrigger>
                  <SelectContent class="max-h-[300px]">
                    <SelectItem
                      v-for="model in availableModels[provider.id] || []"
                      :key="model.id"
                      :value="model.id"
                    >
                      {{ model.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                                
                <!-- Test Connection Button -->
                <Button 
                  size="icon" 
                  variant="outline" 
                  type="button" 
                  :title="$t('system.settings.groups.ai.test_connection')"
                  :disabled="!localFormData[`${provider.id}_api_key`] || testing[provider.id]"
                  @click="testConnection(provider.id)"
                >
                  <Loader2
                    v-if="testing[provider.id]"
                    class="w-4 h-4 animate-spin text-primary"
                  />
                  <Check
                    v-else-if="testSuccess[provider.id]"
                    class="w-4 h-4 text-emerald-600"
                  />
                  <Wifi
                    v-else
                    class="w-4 h-4"
                  />
                </Button>

                <!-- Fetch Models Refresh Button -->
                <Button 
                  size="icon" 
                  variant="ghost" 
                  type="button" 
                  :title="$t('system.settings.groups.ai.fetch_models')"
                  :disabled="loadingModels[provider.id]"
                  @click="fetchModels(provider.id, true)"
                >
                  <RefreshCw
                    class="w-4 h-4"
                    :class="{ 'animate-spin': loadingModels[provider.id] }"
                  />
                </Button>
              </div>
              <p
                v-if="!localFormData[`${provider.id}_api_key`]"
                class="text-xs text-muted-foreground/80 mt-1"
              >
                {{ $t('system.settings.groups.ai.enter_api_key_to_select') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, watch } from 'vue';
import {
  Bot,
  ChevronDown,
  Eye,
  EyeOff,
  ExternalLink,
  Loader2,
  RefreshCw,
  Sparkles,
  Wifi,
  Check
} from 'lucide-vue-next';
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue';
import SettingField from '@/modules/Core/System/components/settings/SettingField.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Button,
    Input
} from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import api from '@/engine/api/client';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Setting {
    id: string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

interface Provider {
    id: string;
    name: string;
    description?: string;
    logo?: string;
    docsUrl?: string;
}

interface Model {
    id: string;
    name: string;
}

interface Props {
    settings: Setting[];
    formData: Record<string, unknown>;
    errors?: Record<string, string[] | string>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
}>();

const localFormData = ref({ ...props.formData });

// Sync local state when prop changes
watch(() => props.formData, (newVal) => {
    localFormData.value = { ...newVal };
}, { deep: true });

const updateField = (key: string, value: unknown) => {
    localFormData.value[key] = value;
    emit('update:formData', { ...localFormData.value });
};

const toast = useToast();
const providers = ref<Provider[]>([
    {
        id: 'gemini',
        name: 'Google Gemini',
        description: 'Google next-generation multimodal AI with high speed & generous free tier.',
        logo: 'https://www.gstatic.com/lamda/images/gemini_sparkle_v002_d4735304ff6292a690345.svg',
        docsUrl: 'https://aistudio.google.com/app/apikey',
    },
    {
        id: 'openai',
        name: 'OpenAI',
        description: 'Industry standard models including GPT-4o, GPT-4o Mini, and o1/o3 reasoning.',
        logo: 'https://openai.com/favicon.ico',
        docsUrl: 'https://platform.openai.com/api-keys',
    },
    {
        id: 'claude',
        name: 'Anthropic Claude',
        description: 'Top-tier writing, coding, and nuanced contextual reasoning with Claude 3.5 Sonnet & Haiku.',
        logo: 'https://claude.ai/favicon.ico',
        docsUrl: 'https://console.anthropic.com/settings/keys',
    },
    {
        id: 'deepseek',
        name: 'DeepSeek',
        description: 'State-of-the-art open-weights model with extremely high performance and low cost.',
        logo: 'https://www.deepseek.com/favicon.ico',
        docsUrl: 'https://platform.deepseek.com/api_keys',
    },
    {
        id: 'grok',
        name: 'xAI Grok',
        description: 'Direct, witty, and real-time knowledge intelligence powered by Elon Musk xAI.',
        logo: 'https://x.ai/favicon.ico',
        docsUrl: 'https://console.x.ai/',
    },
    {
        id: 'openrouter',
        name: 'OpenRouter',
        description: 'Unified single API key giving access to over 200+ models from all top AI labs.',
        logo: 'https://openrouter.ai/favicon.ico',
        docsUrl: 'https://openrouter.ai/keys',
    },
]);

const expandedProvider = ref<string | null>('gemini');
const availableModels = ref<Record<string, Model[]>>({});
const loadingModels = ref<Record<string, boolean>>({});
const testing = ref<Record<string, boolean>>({});
const testSuccess = ref<Record<string, boolean>>({});
const showKey = ref<Record<string, boolean>>({});

const toggleProvider = (id: string) => {
    expandedProvider.value = expandedProvider.value === id ? null : id;
    if (expandedProvider.value === id && (!availableModels.value[id] || availableModels.value[id].length === 0)) {
        fetchModels(id);
    }
};

const toggleKeyVisibility = (id: string) => {
    showKey.value[id] = !showKey.value[id];
};

// Fetch Providers
const fetchProviders = async () => {
    try {
        const response = await api.get('/manage/ai/providers');
        const list = Array.isArray(response.data) ? response.data : response.data?.data;
        if (Array.isArray(list) && list.length > 0) {
            providers.value = list;
        }
        
        // Ensure default provider is set
        if (!localFormData.value.ai_default_provider && providers.value.length > 0) {
            updateField('ai_default_provider', 'gemini');
        }
    } catch (error: unknown) {
        logger.error('Failed to fetch AI providers', error);
    }
};

// Fetch Models
const fetchModels = async (providerId: string, force = false) => {
    if (availableModels.value[providerId] && availableModels.value[providerId].length > 0 && !force) return;

    loadingModels.value = { ...loadingModels.value, [providerId]: true };
    try {
        const apiKey = localFormData.value[`${providerId}_api_key`];
        const response = await api.get(`/manage/ai/models/${providerId}`, {
            params: apiKey ? { api_key: apiKey } : {}
        });
        
        const modelsList = Array.isArray(response.data) ? response.data : (response.data?.data || []);
        if (Array.isArray(modelsList)) {
            availableModels.value = { ...availableModels.value, [providerId]: modelsList };
        }
        
        if (force) {
            toast.success.action(t('system.settings.groups.ai.fetch_success'));
        }
    } catch (error) {
        logger.error(`Failed to fetch models for ${providerId}:`, error);
    } finally {
        loadingModels.value = { ...loadingModels.value, [providerId]: false };
    }
};

// Test Connection
const testConnection = async (providerId: string) => {
    testing.value = { ...testing.value, [providerId]: true };
    testSuccess.value = { ...testSuccess.value, [providerId]: false };
    
    try {
        await api.post('/manage/ai/test', {
            provider: providerId,
            api_key: localFormData.value[`${providerId}_api_key`]
        });
        toast.success.action(t('system.settings.groups.ai.connection_success'));
        testSuccess.value = { ...testSuccess.value, [providerId]: true };
        fetchModels(providerId, true);
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        testing.value = { ...testing.value, [providerId]: false };
    }
};

onMounted(() => {
    fetchProviders();
    fetchModels('gemini');
});
</script>
