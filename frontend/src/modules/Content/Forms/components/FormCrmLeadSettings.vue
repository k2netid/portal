<template>
  <ConsoleFormCard class="p-6 space-y-5" :padded="false">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h3 class="text-lg font-semibold flex items-center gap-2">
          <UserPlus class="h-5 w-5 text-primary" />
          {{ $t('forms.crm.title') }}
        </h3>
        <p class="text-xs text-muted-foreground mt-1 leading-relaxed">
          {{ $t('forms.crm.description') }}
        </p>
      </div>
      <Switch :checked="captureEnabled" @update:checked="onToggleCapture" />
    </div>

    <template v-if="captureEnabled">
      <div class="space-y-2">
        <label class="text-sm font-semibold">{{ $t('forms.crm.assignee') }}</label>
        <Select :model-value="assignedTo || '__none__'" @update:model-value="onAssigneeChange">
          <SelectTrigger class="bg-background/50">
            <SelectValue :placeholder="$t('forms.crm.assigneePlaceholder')" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="__none__">{{ $t('forms.crm.unassigned') }}</SelectItem>
            <SelectItem v-for="user in assignees" :key="user.id" :value="user.id">{{ user.name }}</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="space-y-3">
        <p class="text-sm font-semibold">{{ $t('forms.crm.mappingTitle') }}</p>
        <p class="text-xs text-muted-foreground">{{ $t('forms.crm.mappingHint') }}</p>
        <div v-for="row in mappingRows" :key="row.key" class="grid grid-cols-1 sm:grid-cols-2 gap-2 items-center">
          <span class="text-sm text-muted-foreground">{{ $t(row.labelKey) }}</span>
          <Select :model-value="mapping[row.key] || '__auto__'" @update:model-value="(v) => setMapping(row.key, String(v ?? ''))">
            <SelectTrigger class="h-9 text-xs"><SelectValue /></SelectTrigger>
            <SelectContent>
              <SelectItem value="__auto__">{{ $t('forms.crm.autoField', { field: row.key }) }}</SelectItem>
              <SelectItem v-for="field in formFields" :key="field.name" :value="field.name">
                {{ field.label }} ({{ field.name }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </template>
  </ConsoleFormCard>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { UserPlus } from 'lucide-vue-next';
import { Switch } from '@/shared/components/ui';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui';
import { ConsoleFormCard } from '@/shared/components/shell';
import api from '@/engine/api/client';
import { crmPaths } from '@/engine/api/paths';
import type { FormField } from '../types/forms';

const props = defineProps<{ settings: Record<string, unknown>; formFields?: FormField[] }>();
const emit = defineEmits<{ 'update:settings': [value: Record<string, unknown>] }>();

const assignees = ref<{ id: string; name: string }[]>([]);
const mappingRows = [
  { key: 'first_name', labelKey: 'forms.crm.fields.firstName' },
  { key: 'last_name', labelKey: 'forms.crm.fields.lastName' },
  { key: 'email', labelKey: 'forms.crm.fields.email' },
  { key: 'company', labelKey: 'forms.crm.fields.company' },
] as const;

const captureEnabled = computed({
  get: () => Boolean(props.settings?.crm_lead_capture),
  set: (v: boolean) => patchSettings({ crm_lead_capture: v }),
});

const assignedTo = computed(() => {
  const v = props.settings?.crm_assigned_to;
  return typeof v === 'string' ? v : '';
});

const mapping = computed((): Record<string, string> => {
  const m = props.settings?.crm_lead_mapping;
  return m && typeof m === 'object' && !Array.isArray(m) ? { ...(m as Record<string, string>) } : {};
});

const formFields = computed(() => props.formFields ?? []);

function patchSettings(patch: Record<string, unknown>): void {
  emit('update:settings', { ...props.settings, ...patch });
}

function onToggleCapture(v: boolean): void {
  captureEnabled.value = v;
  if (v && Object.keys(mapping.value).length === 0) {
    patchSettings({ crm_lead_mapping: { email: 'email', first_name: 'name' } });
  }
}

function onAssigneeChange(v: string): void {
  patchSettings({ crm_assigned_to: v === '__none__' ? null : v });
}

function setMapping(key: string, fieldName: string): void {
  const next = { ...mapping.value };
  if (fieldName === '__auto__') delete next[key];
  else next[key] = fieldName;
  patchSettings({ crm_lead_mapping: next });
}

async function loadAssignees(): Promise<void> {
  try {
    const res = await api.get(crmPaths.assignees);
    const rows = res.data?.data?.data ?? res.data?.data ?? [];
    assignees.value = Array.isArray(rows)
      ? rows.map((u: { id: string; name?: string; email?: string }) => ({
          id: String(u.id),
          name: String(u.name ?? u.email ?? u.id),
        }))
      : [];
  } catch {
    assignees.value = [];
  }
}

onMounted(loadAssignees);
</script>
