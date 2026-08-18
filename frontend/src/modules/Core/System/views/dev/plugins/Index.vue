<template>
  <div class="space-y-6">
    <PageHeader
      borderless
      :title="t('infra.plugins.title')"
      :subtitle="t('infra.plugins.subtitle')"
    />
    <ConsoleListCard>
      <div
        v-if="loading"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('infra.plugins.loading') }}
        </p>
      </div>
      <div
        v-else-if="plugins.length === 0"
        class="p-6 text-center"
      >
        <p class="text-muted-foreground">
          {{ t('infra.plugins.empty') }}
        </p>
      </div>
      <div v-else class="overflow-x-auto min-w-0">
      <table
        class="min-w-full divide-y divide-border"
      >
        <thead class="bg-muted">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-foreground/80">
              {{ t('infra.plugins.table.name') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-foreground/80">
              {{ t('infra.plugins.table.version') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-foreground/80">
              {{ t('infra.plugins.table.status') }}
            </th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-foreground/80">
              {{ t('infra.plugins.table.actions') }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-card divide-y divide-border">
          <tr
            v-for="plugin in plugins"
            :key="plugin.id"
            class="hover:bg-muted"
          >
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">
              {{ plugin.name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-muted-foreground">
              {{ plugin.version || '1.0.0' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="plugin.is_active ? 'bg-primary/10 text-primary' : 'bg-secondary text-secondary-foreground'"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              >{{ plugin.is_active ? t('infra.plugins.status.active') : t('infra.plugins.status.inactive') }}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex justify-end space-x-2">
                <button
                  v-if="!plugin.is_active"
                  class="text-primary hover:text-primary/80 font-medium"
                  @click="togglePlugin(plugin)"
                >
                  {{ t('infra.plugins.actions.activate') }}
                </button>
                <button
                  v-else
                  class="text-amber-700 hover:text-amber-800 font-medium"
                  @click="togglePlugin(plugin)"
                >
                  {{ t('infra.plugins.actions.deactivate') }}
                </button>
                <button
                  class="text-indigo-600 hover:text-indigo-900"
                  @click="openSettings(plugin)"
                >
                  {{ t('infra.plugins.actions.settings') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </ConsoleListCard>
    <PluginSettingsModal
      v-if="showSettingsModal"
      :plugin="selectedPlugin"
      @close="showSettingsModal = false"
      @saved="handleSettingsSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { PageHeader } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { systemPaths } from '@/engine/api/paths';
import { useToast } from '@/shared/composables/useToast';
import PluginSettingsModal from '@/modules/Core/System/components/plugins/PluginSettingsModal.vue';
import { parseResponse, ensureArray } from '@/shared/utils/responseParser';

const { t } = useI18n();
const toast = useToast();

interface Plugin {
    id: string;
    name: string;
    slug: string;
    version?: string;
    is_active: boolean;
    settings?: Record<string, unknown>;
}

const plugins = ref<Plugin[]>([]);
const loading = ref(false);
const showSettingsModal = ref(false);
const selectedPlugin = ref<Plugin | null>(null);

const fetchPlugins = async () => {
    loading.value = true;
    try {
        const response = await api.get(systemPaths.plugins);
        const { data } = parseResponse<Plugin[]>(response);
        plugins.value = ensureArray(data);
    } catch (error: unknown) {
        logger.error('Failed to fetch plugins:', error);
        toast.error.default(t('infra.plugins.messages.failed_fetch'));
    } finally {
        loading.value = false;
    }
};

const togglePlugin = async (plugin: Plugin) => {
    try {
        if (plugin.is_active) {
            await api.post(systemPaths.pluginDeactivate(plugin.id));
            plugin.is_active = false;
            toast.success.action(t('infra.plugins.messages.deactivated'));
        } else {
            await api.post(systemPaths.pluginActivate(plugin.id));
            plugin.is_active = true;
            toast.success.action(t('infra.plugins.messages.activated'));
        }
    } catch (error: unknown) {
        logger.error('Failed to toggle plugin:', error);
        toast.error.fromResponse(error);
    }
};

const openSettings = (plugin: Plugin) => {
    selectedPlugin.value = plugin;
    showSettingsModal.value = true;
};

const handleSettingsSaved = () => {
    fetchPlugins();
    showSettingsModal.value = false;
};

onMounted(() => {
    fetchPlugins();
});
</script>

