<template>
  <div class="space-y-6 max-w-2xl">
    <PageHeader
      borderless
      :title="isEdit ? t('infra.dynamic.record.editTitle') : t('infra.dynamic.record.newTitle')"
    >
      <template #subtitle>
        <p class="text-sm text-muted-foreground">{{ t('infra.dynamic.record.subtitle') }}</p>
        <p class="text-xs text-muted-foreground mt-1">
          <RouterLink :to="{ name: 'dynamic-records-index', params: { slug } }" class="hover:underline">{{ contentType?.name ?? slug }}</RouterLink>
        </p>
      </template>
    </PageHeader>

    <div
      v-if="loading"
      class="text-sm text-muted-foreground"
    >
      Loading…
    </div>
    <p
      v-else-if="error"
      class="text-sm text-destructive"
    >
      {{ error }}
    </p>
    <form
      v-else
      @submit.prevent="submit"
    >
      <ConsoleFormCard class="space-y-4 p-4" :padded="false">
      <DynamicFieldInput
        v-for="field in contentType?.fields ?? []"
        :key="field.slug"
        :field="field"
        :model-value="form[field.slug]"
        @update:model-value="(v) => (form[field.slug] = v)"
      />
      <div class="flex gap-2 pt-2">
        <Button
          type="submit"
          :disabled="saving"
        >
          {{ saving ? 'Saving…' : 'Save' }}
        </Button>
        <Button
          type="button"
          variant="outline"
          @click="router.push({ name: 'dynamic-records-index', params: { slug } })"
        >
          Cancel
        </Button>
      </div>
      </ConsoleFormCard>
    </form>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';
const { t } = useI18n();
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { Button } from '@/shared/components/ui';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import DynamicFieldInput from '../../components/dynamic/DynamicFieldInput.vue';
import CckService, { type CckContentType } from '../../services/cckService';
import DynamicRecordService, { type DynamicRecordRow } from '../../services/dynamicRecordService';

const route = useRoute();
const router = useRouter();

const slug = computed(() => String(route.params.slug ?? ''));
const recordId = computed(() => {
    const id = route.params.recordId;
    return id && id !== 'new' ? String(id) : null;
});
const isEdit = computed(() => Boolean(recordId.value));

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const contentType = ref<CckContentType | null>(null);
const form = reactive<Record<string, unknown>>({});

function initFormFields(type: CckContentType): void {
    for (const field of type.fields ?? []) {
        if (!(field.slug in form)) {
            form[field.slug] = field.type === 'boolean' ? false : '';
        }
    }
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';
    try {
        const typeRes = await CckService.getTypeBySlug(slug.value);
        contentType.value = parseSingleResponse<CckContentType>(typeRes);
        if (!contentType.value) {
            error.value = 'Content type not found';
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
        error.value = e instanceof Error ? e.message : 'Failed to load';
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
        } else {
            await DynamicRecordService.create(slug.value, payload);
        }
        await router.push({ name: 'dynamic-records-index', params: { slug: slug.value } });
    } catch (e: unknown) {
        error.value = e instanceof Error ? e.message : 'Save failed';
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
