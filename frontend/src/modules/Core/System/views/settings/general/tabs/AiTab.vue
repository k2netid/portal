<template>
  <div class="space-y-6">
    <SettingGroup
      :title="$t('system.settings.ai.title')"
      :description="$t('system.settings.ai.description')"
      :icon="Sparkles"
      color="indigo"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Enable AI -->
        <SettingField
          v-model="(localFormData.ai_enabled as any)"
          field-key="ai_enabled"
          type="boolean"
          :label="$t('system.settings.ai.enable_ai')"
          :description="$t('system.settings.ai.enable_ai_desc')"
          :error="errors?.ai_enabled"
          @update:model-value="v => updateField('ai_enabled', v)"
        />
                
        <!-- Default Provider -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.ai.default_provider') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.ai.default_provider_desc') }}
          </p>
          <Select 
            :model-value="(localFormData.ai_default_provider as string)" 
            :disabled="!localFormData.ai_enabled"
            @update:model-value="v => updateField('ai_default_provider', v)"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('system.settings.ai.default_provider')" />
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
                    class="w-4 h-4 object-contain"
                  >
                  {{ provider.name }}
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
      <h3 class="text-lg font-medium">
        {{ $t('system.settings.ai.providers') }}
      </h3>
            
      <div
        v-for="provider in providers"
        :key="provider.id"
        class="border rounded-lg overflow-hidden bg-card text-card-foreground shadow-sm"
      >
        <button 
          type="button"
          class="w-full flex items-center justify-between p-4 hover:bg-muted/50 transition-colors text-left"
          @click="toggleProvider(provider.id)"
        >
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-md bg-muted flex items-center justify-center p-1.5 border">
              <img
                v-if="provider.logo"
                :src="provider.logo"
                class="w-full h-full object-contain"
              >
              <Bot
                v-else
                class="w-5 h-5 text-muted-foreground"
              />
            </div>
            <div>
              <span class="font-medium block">{{ provider.name }}</span>
              <span
                v-if="formData[`${provider.id}_model`]"
                class="text-xs text-muted-foreground"
              >
                {{ $t('system.settings.groups.ai.using_model', { model: formData[`${provider.id}_model`] }) }}
              </span>
            </div>
          </div>
          <ChevronDown 
            class="w-5 h-5 text-muted-foreground transition-transform duration-200" 
            :class="{ 'rotate-180': expandedProvider === provider.id }"
          />
        </button>

        <div
          v-show="expandedProvider === provider.id"
          class="p-4 border-t bg-muted/10"
        >
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Col: API Key -->
            <div class="space-y-1">
              <label class="block text-sm font-medium text-foreground">
                {{ $t('system.settings.ai.api_key') }}
              </label>
              <div class="relative">
                <Input 
                  :type="showKey[provider.id] ? 'text' : 'password'"
                  :placeholder="$t('system.settings.ai.api_key_placeholder')"
                  :model-value="(localFormData[`${provider.id}_api_key`] as string)"
                  class="pr-10"
                  @update:model-value="v => updateField(`${provider.id}_api_key`, v)"
                />
                <button 
                  type="button"
                  class="absolute right-0 top-0 h-full px-3 text-muted-foreground hover:text-foreground transition-colors"
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
                class="text-xs text-muted-foreground"
              >
                {{ Array.isArray(errors?.[`${provider.id}_api_key` || '']) ? (errors?.[`${provider.id}_api_key` || ''] as string[])[0] : errors?.[`${provider.id}_api_key` || ''] }}
              </p>
            </div>

            <!-- Right Col: Model Selection & Actions -->
            <div class="space-y-1">
              <label class="block text-sm font-medium text-foreground">
                {{ $t('system.settings.ai.model_select') }}
              </label>
              <div class="flex gap-2">
                <Select 
                  :model-value="(localFormData[`${provider.id}_model`] as string)" 
                  :disabled="!localFormData[`${provider.id}_api_key`] || loadingModels[provider.id]"
                  @update:model-value="v => updateField(`${provider.id}_model`, v)"
                  @update:open="(open) => { if(open) fetchModels(provider.id) }"
                >
                  <SelectTrigger class="w-full">
                    <SelectValue :placeholder="loadingModels[provider.id] ? $t('system.settings.groups.ai.loading_models') : ((formData[`${provider.id}_model`] as string) || $t('system.settings.groups.ai.select_model'))" />
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
                                
                <!-- Text Connection -->
                <Button 
                  size="icon" 
                  variant="outline" 
                  type="button" 
                  :title="$t('system.settings.ai.test_connection')"
                  :disabled="!formData[`${provider.id}_api_key`] || testing[provider.id]"
                  @click="testConnection(provider.id)"
                >
                  <Loader2
                    v-if="testing[provider.id]"
                    class="w-4 h-4 animate-spin"
                  />
                  <Wifi
                    v-else
                    class="w-4 h-4"
                    :class="testing[provider.id] === false ? 'text-green-600' : ''"
                  /> <!-- showing green if just tested successfully? naive logic --> 
                </Button>

                <!-- Fetch Models -->
                <Button 
                  size="icon" 
                  variant="ghost" 
                  type="button" 
                  :title="$t('system.settings.ai.fetch_models')"
                  :disabled="!formData[`${provider.id}_api_key`] || loadingModels[provider.id]"
                  @click="fetchModels(provider.id, true)"
                >
                  <RefreshCw
                    class="w-4 h-4"
                    :class="{ 'animate-spin': loadingModels[provider.id] }"
                  />
                </Button>
              </div>
              <p
                v-if="!formData[`${provider.id}_api_key`]"
                class="text-xs text-muted-foreground mt-1"
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
  Loader2,
  RefreshCw,
  Sparkles,
  Wifi,
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
    id: string | string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

interface Provider {
    id: string;
    name: string;
    logo?: string;
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
const providers = ref<Provider[]>([]);
const expandedProvider = ref<string | null>('gemini'); // Default expand
const availableModels = ref<Record<string, Model[]>>({});
const loadingModels = ref<Record<string, boolean>>({});
const testing = ref<Record<string, boolean>>({});
const testSuccess = ref<Record<string, boolean>>({});
const showKey = ref<Record<string, boolean>>({});

const toggleProvider = (id: string) => {
    expandedProvider.value = expandedProvider.value === id ? null : id;
};

const toggleKeyVisibility = (id: string) => {
    showKey.value[id] = !showKey.value[id];
};

// Fetch Providers
const fetchProviders = async () => {
    try {
        const response = await api.get('/manage/ai/providers');
        providers.value = response.data;
        
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
    // Return early if already loaded and not forcing
    if (availableModels.value[providerId] && availableModels.value[providerId].length > 0 && !force) return;
    
    // Return if no API key
    if (!props.formData[`${providerId}_api_key`]) return;

    loadingModels.value = { ...loadingModels.value, [providerId]: true };
    try {
        // Pass API key if available in form data, to allow fetching without saving first
        const apiKey = props.formData[`${providerId}_api_key`];
        const response = await api.get(`/manage/ai/models/${providerId}`, {
            params: { api_key: apiKey }
        });
        
        availableModels.value = { ...availableModels.value, [providerId]: response.data };
        
        if (force) {
            toast.success.action(t('system.settings.groups.ai.fetch_success'));
        }
    } catch {
        // If 401/403, might be invalid key
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
            api_key: props.formData[`${providerId}_api_key`]
        });
        toast.success.action(t('system.settings.groups.ai.connection_success'));
        testSuccess.value = { ...testSuccess.value, [providerId]: true };
        fetchModels(providerId, true); // Auto fetch models on success
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        testing.value = { ...testing.value, [providerId]: false };
    }
};

onMounted(() => {
    fetchProviders();
});
</script>
