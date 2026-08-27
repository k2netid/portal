<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { analyticsPaths } from '@/engine/api/paths';
import { parseResponse, parseSingleResponse, ensureArray } from '@/shared/utils/responseParser';
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue';
import SettingField from '@/modules/Core/System/components/settings/SettingField.vue';
import { Button } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import { AnalyticsService } from '@/shared/services/analyticsService';
import { BarChart3, Trash2 as TrashIcon } from 'lucide-vue-next';
import type { SettingValue } from '@/engine/types/settings';

interface Setting {
    id: string | number;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();

const settings = ref<Setting[]>([]);
const formData = ref<Record<string, SettingValue>>({});
const saving = ref(false);
const cleaning = ref(false);
const purging = ref(false);

const retentionKeys = [
    'analytics_retention_days',
    'analytics_event_retention_days',
    'analytics_visitor_retention_days',
] as const;

const ensureSetting = (key: string, defaultValue: unknown, type: string): void => {
    if (settings.value.some((row) => row.key === key)) {
        return;
    }
    settings.value.push({
        id: `tmp-${key}`,
        key,
        value: defaultValue,
        type,
        group: 'analytics',
    });
};

const load = async (): Promise<void> => {
    try {
        const response = await api.get(analyticsPaths.settings);
        const { data } = parseResponse(response);
        settings.value = ensureArray(data) as Setting[];
        ensureSetting('analytics_retention_days', 90, 'integer');
        ensureSetting('analytics_event_retention_days', 30, 'integer');
        ensureSetting('analytics_visitor_retention_days', 365, 'integer');
        const next: Record<string, SettingValue> = {};
        for (const row of settings.value) {
            next[row.key] = row.value as SettingValue;
        }
        formData.value = next;
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    }
};

const updateField = (key: string, value: SettingValue): void => {
    formData.value = { ...formData.value, [key]: value };
};

const save = async (): Promise<void> => {
    saving.value = true;
    try {
        const payload = settings.value
            .filter((row) => retentionKeys.includes(row.key as typeof retentionKeys[number]))
            .map((row) => ({
                key: row.key,
                value: formData.value[row.key],
                type: row.type,
                group: 'analytics',
            }));
        await api.post(analyticsPaths.settingsBulkUpdate, { settings: payload });
        toast.success.save();
        await load();
    } catch (error: unknown) {
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

const handleCleanup = async (): Promise<void> => {
    const isConfirmed = await confirm({
        title: t('system.settings.groups.analytics.cleanupTitle'),
        message: t('system.settings.groups.analytics.cleanupConfirm'),
        variant: 'destructive',
        confirmText: t('system.settings.groups.analytics.cleanupButton'),
    });
    if (!isConfirmed) {
        return;
    }
    cleaning.value = true;
    try {
        const response = await AnalyticsService.cleanup();
        const payload = parseSingleResponse<{ total_deleted?: number }>(response);
        const count = typeof payload?.total_deleted === 'number' ? payload.total_deleted : 0;
        toast.success.action(t('system.settings.groups.analytics.cleanupSuccess', { count }));
    } catch (error) {
        toast.error.fromResponse(error);
    } finally {
        cleaning.value = false;
    }
};

const handlePurgeAll = async (): Promise<void> => {
    const isConfirmed = await confirm({
        title: t('system.settings.groups.analytics.purgeTitle'),
        message: t('system.settings.groups.analytics.purgeConfirm'),
        variant: 'destructive',
        confirmText: t('system.settings.groups.analytics.purgeButton'),
    });
    if (!isConfirmed) {
        return;
    }
    purging.value = true;
    try {
        const response = await AnalyticsService.purgeAll('RESET_ALL_ANALYTICS');
        const payload = parseSingleResponse<{ total_deleted?: number }>(response);
        const count = typeof payload?.total_deleted === 'number' ? payload.total_deleted : 0;
        toast.success.action(t('system.settings.groups.analytics.purgeSuccess', { count }));
    } catch (error) {
        toast.error.fromResponse(error);
    } finally {
        purging.value = false;
    }
};

onMounted(() => {
    void load();
});
</script>

<template>
  <div class="space-y-4">
    <SettingGroup
      :title="t('system.settings.groups.analytics.retentionTitle')"
      :description="t('system.settings.groups.analytics.description')"
      :icon="BarChart3"
      color="indigo"
      :default-expanded="true"
    >
      <SettingField
        v-for="key in retentionKeys"
        :key="key"
        :model-value="(formData[key] as never)"
        :field-key="key"
        :label="$t('system.settings.labels.' + key)"
        :description="$t('system.settings.descriptions.' + key)"
        type="integer"
        :enabled-text="$t('system.settings.enabled')"
        :disabled-text="$t('system.settings.disabled')"
        @update:model-value="(value) => updateField(key, value)"
      />
    </SettingGroup>

    <div class="flex justify-end">
      <Button
        type="button"
        :disabled="saving"
        @click="save"
      >
        {{ saving ? t('system.settings.saving') : t('system.settings.save') }}
      </Button>
    </div>

    <div class="p-6 bg-card border border-destructive/20 rounded-lg">
      <div class="flex items-start gap-4 mb-6">
        <div class="p-2 bg-destructive/10 rounded-full">
          <TrashIcon class="w-5 h-5 text-destructive" />
        </div>
        <div>
          <h3 class="text-lg font-medium text-destructive">
            {{ $t('system.settings.groups.analytics.cleanupTitle') }}
          </h3>
          <p class="text-sm text-muted-foreground mt-1">
            {{ $t('system.settings.groups.analytics.cleanupDescription') }}
          </p>
        </div>
      </div>
      <div class="flex flex-wrap gap-3 justify-start">
        <Button
          type="button"
          variant="destructive"
          :disabled="cleaning || purging"
          @click="handleCleanup"
        >
          <TrashIcon class="w-4 h-4 mr-2" />
          {{ cleaning ? $t('system.settings.groups.analytics.cleaningNow') : $t('system.settings.groups.analytics.cleanupButton') }}
        </Button>
        <Button
          type="button"
          variant="outline"
          class="border-destructive/40 text-destructive hover:bg-destructive/10"
          :disabled="cleaning || purging"
          @click="handlePurgeAll"
        >
          <TrashIcon class="w-4 h-4 mr-2" />
          {{ purging ? $t('system.settings.groups.analytics.purgingNow') : $t('system.settings.groups.analytics.purgeButton') }}
        </Button>
      </div>
    </div>
  </div>
</template>
