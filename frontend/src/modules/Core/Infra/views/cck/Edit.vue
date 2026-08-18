<template>
  <div class="space-y-6 max-w-5xl">
    <PageHeader
      borderless
      :title="isCreate ? $t('infra.cck.newType') : $t('infra.cck.editType')"
      :subtitle="cckSubtitle"
    />

    <div
      v-if="loading"
      class="text-sm text-muted-foreground"
    >
      {{ $t('common.messages.loading.default') }}
    </div>
    <form
      v-else
      class="space-y-6"
      @submit.prevent="handleSave"
    >
      <ConsoleFormCard :padded="false" class="pt-6 grid gap-4 sm:grid-cols-2">
          <div>
            <Label>Name</Label>
            <Input
              aria-label="Name"
              v-model="form.name"
              required
              @blur="maybeSlugFromName"
            />
          </div>
          <div>
            <Label>Slug</Label>
            <Input
              aria-label="Slug"
              v-model="form.slug"
              class="font-mono"
              required
            />
          </div>
          <div class="sm:col-span-2">
            <Label>Description</Label>
            <Textarea
              aria-label="Description"
              v-model="form.description"
              rows="2"
            />
          </div>
          <label
            v-if="!isCreate"
            class="flex items-center gap-2 text-sm sm:col-span-2"
          >
            <Checkbox
              :aria-label="$t('common.labels.active')"
              :checked="form.is_active"
              @update:checked="form.is_active = $event === true"
            />
            Active
          </label>
      </ConsoleFormCard>

      <ConsoleFormCard :title="$t('infra.cck.fields')">
        <CckTypeBuilder v-model="form.fields" />
      </ConsoleFormCard>

      <ConsoleFormCard
        v-if="validationPreview"
        :title="$t('infra.cck.validationPreview')"
      >
        <pre class="text-xs font-mono bg-muted/40 p-3 rounded-md overflow-x-auto">{{ validationPreview }}</pre>
      </ConsoleFormCard>

      <p
        v-if="message"
        class="text-sm"
        :class="messageOk ? 'text-foreground' : 'text-red-800'"
      >
        {{ message }}
      </p>

      <div class="flex flex-wrap gap-2">
        <Button
          type="submit"
          :disabled="saving"
        >
          {{ saving ? 'Saving…' : $t('infra.cck.save') }}
        </Button>
        <Button
          type="button"
          variant="outline"
          :disabled="isCreate || saving"
          @click="loadValidationRules"
        >
          Preview rules
        </Button>
        <Button
          type="button"
          variant="outline"
          @click="router.push({ name: 'cck-index' })"
        >
          Back
        </Button>
        <Button
          v-if="!isCreate"
          type="button"
          variant="outline" class="text-red-800 border-red-200 hover:bg-red-50"
          :disabled="saving"
          @click="handleDelete"
        >
          {{ $t('infra.cck.delete') }}
        </Button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import {
    Button,
    Checkbox,
    Input,
    Label,
    Textarea,
} from '@/shared/components/ui';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import CckTypeBuilder from '../../components/cck/CckTypeBuilder.vue';
import CckService, { type CckContentType, type CckFieldDefinition } from '../../services/cckService';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const typeId = computed(() => route.params.id as string | undefined);
const isCreate = computed(() => route.name === 'cck-create');

const loading = ref(!isCreate.value);
const saving = ref(false);
const message = ref('');
const messageOk = ref(false);
const validationPreview = ref('');

const cckSubtitle = computed(() => {
  const base = t('infra.cck.subtitle');
  const slug = form.value.slug;
  if (!slug) return base;
  return `${base} — ${t('infra.cck.apiHint', { slug })}`;
});

const form = ref({
    name: '',
    slug: '',
    description: '',
    is_active: true,
    fields: [] as CckFieldDefinition[],
});

function slugify(text: string): string {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function maybeSlugFromName(): void {
    if (isCreate.value && !form.value.slug) {
        form.value.slug = slugify(form.value.name);
    }
}

async function loadType(): Promise<void> {
    if (!typeId.value) {
        return;
    }
    loading.value = true;
    try {
        const response = await CckService.getType(typeId.value);
        const payload = parseSingleResponse<CckContentType>(response);
        if (!payload) {
            throw new Error('Content type not found');
        }
        form.value = {
            name: payload.name,
            slug: payload.slug,
            description: payload.description ?? '',
            is_active: payload.is_active !== false,
            fields: payload.fields ?? [],
        };
    } catch (e: unknown) {
        message.value = e instanceof Error ? e.message : t('infra.cck.messages.loadFailed');
        messageOk.value = false;
    } finally {
        loading.value = false;
    }
}

async function loadValidationRules(): Promise<void> {
    if (!typeId.value) {
        return;
    }
    const response = await CckService.validationRules(typeId.value);
    const payload = parseSingleResponse<{ validation_rules: Record<string, string> }>(response);
    validationPreview.value = JSON.stringify(payload?.validation_rules ?? {}, null, 2);
}

async function handleSave(): Promise<void> {
    saving.value = true;
    message.value = '';
    const payload = {
        name: form.value.name,
        slug: form.value.slug,
        description: form.value.description || null,
        fields: form.value.fields,
        ...(isCreate.value ? {} : { is_active: form.value.is_active }),
    };
    try {
        if (isCreate.value) {
            const response = await CckService.createType(payload);
            const created = parseSingleResponse<CckContentType>(response);
            messageOk.value = true;
            message.value = t('infra.cck.messages.created');
            if (created?.id) {
                await router.replace({ name: 'cck-edit', params: { id: created.id } });
            }
        } else if (typeId.value) {
            await CckService.updateType(typeId.value, payload);
            messageOk.value = true;
            message.value = t('infra.cck.messages.saved');
            await loadValidationRules();
        }
    } catch (e: unknown) {
        messageOk.value = false;
        message.value = e instanceof Error ? e.message : t('infra.cck.messages.saveFailed');
    } finally {
        saving.value = false;
    }
}

async function handleDelete(): Promise<void> {
    if (!typeId.value || !window.confirm('Delete this content type?')) {
        return;
    }
    saving.value = true;
    try {
        await CckService.deleteType(typeId.value);
        await router.push({ name: 'cck-index' });
    } catch (e: unknown) {
        message.value = e instanceof Error ? e.message : 'Delete failed';
        messageOk.value = false;
    } finally {
        saving.value = false;
    }
}

onMounted(async () => {
    if (!isCreate.value) {
        await loadType();
        await loadValidationRules();
    }
});
</script>
