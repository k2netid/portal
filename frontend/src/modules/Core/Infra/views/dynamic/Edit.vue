<template>
  <div class="space-y-6 w-full">
    <PageHeader
      borderless
      :title="isEdit ? $t('infra.dynamic.record.editTitle', { name: contentType?.name ?? slug }) : $t('infra.dynamic.record.newTitle', { name: contentType?.name ?? slug })"
      :subtitle="$t('infra.dynamic.record.subtitle', { name: contentType?.name ?? slug })"
    >
      <template #actions>
        <Button
          variant="ghost"
          size="sm"
          class="h-9 gap-1.5 text-xs text-muted-foreground hover:text-foreground"
          @click="router.push({ name: 'dynamic-records-index', params: { slug } })"
        >
          <ArrowLeft class="h-3.5 w-3.5" />
          {{ $t('infra.dynamic.record.backToRecords') }}
        </Button>
      </template>
    </PageHeader>

    <!-- Loading State -->
    <div v-if="loading" class="p-12 text-center text-sm text-muted-foreground flex flex-col items-center justify-center gap-3">
      <Spinner class="h-6 w-6 text-primary" />
      <span>{{ $t('common.messages.loading.default') }}</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-6">
      <Alert variant="destructive">
        <AlertCircle class="h-4 w-4" />
        <AlertTitle>{{ $t('common.labels.error') }}</AlertTitle>
        <AlertDescription>{{ error }}</AlertDescription>
      </Alert>
    </div>

    <!-- Record Form -->
    <form
      v-else
      class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start"
      @submit.prevent="submit"
    >
      <!-- Main Form Fields -->
      <div class="lg:col-span-2 space-y-6">
        <ConsoleFormCard
          :title="contentType?.name || 'Fields'"
          :subtitle="$t('infra.dynamic.record.subtitle', { name: contentType?.name ?? slug })"
        >
          <div class="space-y-4">
            <DynamicFieldInput
              v-for="field in contentType?.fields ?? []"
              :key="field.slug"
              :field="field"
              :model-value="form[field.slug]"
              @update:model-value="(v) => (form[field.slug] = v)"
            />
          </div>
        </ConsoleFormCard>
      </div>

      <!-- Sidebar Metadata & Actions -->
      <div class="space-y-6">
        <ConsoleFormCard
          :title="$t('infra.models.form.general')"
          class="space-y-4"
        >
          <div class="space-y-3 text-xs">
            <div class="flex items-center justify-between pb-2 border-b border-border/50">
              <span class="text-muted-foreground">Model</span>
              <span class="font-medium text-foreground">{{ contentType?.name }}</span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-border/50">
              <span class="text-muted-foreground">Slug</span>
              <span class="font-mono text-foreground font-medium">{{ contentType?.slug }}</span>
            </div>
            <div class="flex items-center justify-between pb-2 border-b border-border/50">
              <span class="text-muted-foreground">Fields</span>
              <Badge variant="outline" class="text-[11px] font-normal">
                {{ (contentType?.fields ?? []).length }} fields
              </Badge>
            </div>
            <div class="flex flex-col gap-1 pt-1">
              <span class="text-muted-foreground text-[11px]">API Route</span>
              <div class="p-2 rounded bg-muted/50 font-mono text-[11px] text-foreground truncate">
                /api/v1/dynamic/{{ slug }}
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-2 pt-4 border-t border-border/60">
            <Button
              type="submit"
              size="sm"
              class="h-9 gap-2 text-xs w-full"
              :disabled="saving"
            >
              <Spinner v-if="saving" class="h-3.5 w-3.5" />
              <Save v-else class="h-3.5 w-3.5" />
              {{ saving ? $t('infra.dynamic.record.saving') : $t('infra.dynamic.record.save') }}
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="h-9 text-xs w-full"
              @click="router.push({ name: 'dynamic-records-index', params: { slug } })"
            >
              {{ $t('infra.dynamic.record.cancel') }}
            </Button>
          </div>
        </ConsoleFormCard>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { ArrowLeft, Save, AlertCircle } from 'lucide-vue-next';
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';
import { Button, Spinner, Alert, AlertTitle, AlertDescription, Badge } from '@/shared/components/ui';
import { useToast } from '@/shared/composables/useToast';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import DynamicFieldInput from '../../components/dynamic/DynamicFieldInput.vue';
import DataModelService, { type DataModelSchema } from '../../services/dataModelService';
import DynamicRecordService, { type DynamicRecordRow } from '../../services/dynamicRecordService';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const toast = useToast();

const slug = computed(() => String(route.params.slug ?? ''));
const recordId = computed(() => {
    const id = route.params.recordId;
    return id && id !== 'new' ? String(id) : null;
});
const isEdit = computed(() => Boolean(recordId.value));

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const contentType = ref<DataModelSchema | null>(null);
const form = reactive<Record<string, unknown>>({});

function initFormFields(type: DataModelSchema): void {
    for (const field of type.fields ?? []) {
        if (!(field.slug in form)) {
            if (field.type === 'boolean') {
                form[field.slug] = false;
            } else if (field.type === 'json') {
                form[field.slug] = null;
            } else {
                form[field.slug] = '';
            }
        }
    }
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const typeRes = await DataModelService.getTypeBySlug(slug.value);
        contentType.value = parseSingleResponse<DataModelSchema>(typeRes);
        if (!contentType.value) {
            error.value = 'Data model not found';
            return;
        }
        initFormFields(contentType.value);

        if (recordId.value) {
            const recRes = await DynamicRecordService.get(slug.value, recordId.value);
            const record = parseSingleResponse<DynamicRecordRow>(recRes);
            if (record?.data) {
                for (const [key, value] of Object.entries(record.data)) {
                    form[key] = value;
                }
            }
        }
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : t('infra.dynamic.record.messages.loadFailed');
    } finally {
        loading.value = false;
    }
}

async function submit(): Promise<void> {
    saving.value = true;
    error.value = '';
    const payload: Record<string, unknown> = {};
    for (const field of contentType.value?.fields ?? []) {
        payload[field.slug] = form[field.slug];
    }
    try {
        if (recordId.value) {
            await DynamicRecordService.update(slug.value, recordId.value, payload);
            toast.success.default(t('infra.dynamic.record.messages.saved'));
        } else {
            await DynamicRecordService.create(slug.value, payload);
            toast.success.default(t('infra.dynamic.record.messages.created'));
        }
        await router.push({ name: 'dynamic-records-index', params: { slug: slug.value } });
    } catch (e: unknown) {
        toast.error.default(e instanceof Error ? e.message : t('infra.dynamic.record.messages.saveFailed'));
    } finally {
        saving.value = false;
    }
}

watch(
    () => route.fullPath,
    () => {
        load();
    },
);

onMounted(() => {
    load();
});
</script>
