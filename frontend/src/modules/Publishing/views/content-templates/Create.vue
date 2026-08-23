<template>
  <div class="console-page min-w-0 max-w-full">
    <PageHeader
      :title="t('publishing.content_templates.form.createTitle')"
      borderless
      :subtitle="t('publishing.content_templates.form.createSubtitle')"
    >
      <template #actions>
        <Button
          variant="ghost"
          size="sm"
          @click="router.push({ name: 'contents.index', query: { tab: 'templates' } })"
        >
          <ChevronLeft data-icon="inline-start" class="size-4 shrink-0" />
          {{ t('publishing.content_templates.form.back') }}
        </Button>
      </template>
    </PageHeader>

    <form
      class="pb-10"
      @submit.prevent="handleSubmit"
    >
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content (Left) -->
        <div class="lg:col-span-2 space-y-6">
          <ConsoleFormCard :title="t('publishing.content_templates.form.content')">
              <div class="space-y-2">
                <Label for="title">
                  {{ t('publishing.content_templates.form.titleLabel') }}
                </Label>
                <Input
                  id="title"
                  v-model="form.title_template"
                  :placeholder="t('publishing.content_templates.form.titlePlaceholder')"
                />
              </div>

              <div class="space-y-2">
                <Label>
                  {{ t('publishing.content_templates.form.body') }}
                </Label>
                <TiptapEditor
                  :model-value="form.body_template"
                  :placeholder="t('publishing.content_templates.form.bodyPlaceholder')"
                  @update:model-value="(val) => form.body_template = val"
                />
              </div>

              <div class="space-y-2">
                <Label for="excerpt">
                  {{ t('publishing.content_templates.form.excerpt') }}
                </Label>
                <Textarea
                  id="excerpt"
                  v-model="form.excerpt_template"
                  rows="3"
                  :placeholder="t('publishing.content_templates.form.excerptPlaceholder')"
                />
              </div>
          </ConsoleFormCard>
        </div>

        <!-- Sidebar (Right) -->
        <div class="space-y-6">
          <ConsoleFormCard :title="t('publishing.content_templates.form.details')">
              <div class="space-y-2">
                <Label for="name">
                  {{ t('publishing.content_templates.form.name') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                  id="name"
                  v-model="form.name"
                  required
                  :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
                  :placeholder="t('publishing.content_templates.form.namePlaceholder')"
                  @input="generateSlug"
                />
                <p
                  v-if="errors.name"
                  class="text-sm text-destructive"
                >
                  {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="slug">
                  {{ t('publishing.content_templates.form.slug') || 'Slug' }} <span class="text-destructive">*</span>
                </Label>
                <Input
                  id="slug"
                  v-model="form.slug"
                  required
                  :class="{ 'border-destructive focus-visible:ring-destructive': errors.slug }"
                  :placeholder="t('common.placeholders.slugTemplate')"
                />
                <p class="text-xs text-muted-foreground">
                  {{ t('publishing.content_templates.form.slugHelp') || 'URL-friendly version' }}
                </p>
                <p
                  v-if="errors.slug"
                  class="text-sm text-destructive"
                >
                  {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="description">
                  {{ t('publishing.content_templates.form.description') }}
                </Label>
                <Textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  :placeholder="t('publishing.content_templates.form.descriptionPlaceholder')"
                />
              </div>

              <div class="space-y-2">
                <Label for="type">
                  {{ t('publishing.content_templates.form.type') }} <span class="text-destructive">*</span>
                </Label>
                <Select
                  v-model="form.type"
                  required
                >
                  <SelectTrigger id="type">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="post">
                      {{ t('publishing.content_templates.types.post') }}
                    </SelectItem>
                    <SelectItem value="page">
                      {{ t('publishing.content_templates.types.page') }}
                    </SelectItem>
                    <SelectItem value="custom">
                      {{ t('publishing.content_templates.types.custom') }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
          </ConsoleFormCard>

          <div class="flex items-center gap-2">
            <div class="flex-1" />
            <Button
              variant="outline"
              size="sm"
              type="button"
              @click="router.push({ name: 'contents.index', query: { tab: 'templates' } })"
            >
              {{ t('publishing.content_templates.form.cancel') }}
            </Button>
            <Button
              type="submit"
              size="sm"
              :disabled="saving || !isValid"
            >
              <Loader2
                v-if="saving"
                data-icon="inline-start" class="size-4 shrink-0 animate-spin"
              />
              <Save
                v-else
                data-icon="inline-start" class="size-4 shrink-0"
              />
              {{ saving ? t('publishing.content_templates.form.saving') : t('publishing.content_templates.form.save') }}
            </Button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';

import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { contentTemplateSchema } from '@/shared/schemas';
import { Button, Input, Label, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Textarea } from '@/shared/components/ui';

import TiptapEditor from '@/shared/components/editor/TiptapEditor.vue';
import {
  ChevronLeft,
  Loader2,
  Save,
} from 'lucide-vue-next';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(contentTemplateSchema);
const saving = ref(false);

const form = ref({
    name: '',
    slug: '',
    description: '',
    type: 'post' as 'post' | 'page' | 'custom',
    title_template: '',
    body_template: '',
    excerpt_template: '',
});

const isValid = computed(() => {
    return form.value.name?.trim() && form.value.type && form.value.slug?.trim();
});

const generateSlug = () => {
    if (!form.value.slug || form.value.slug === slugify(form.value.name.slice(0, -1))) {
         form.value.slug = slugify(form.value.name);
    }
};

const slugify = (text: string) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w-\s]+/g, '')
        .replace(/-+/, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
};

const handleSubmit = async () => {
    if (!validateWithZod(form.value)) return;

    saving.value = true;
    clearErrors();
    try {
        await api.post('/manage/publishing/content-templates', form.value);
        toast.success.create(t('publishing.content_templates.title_singular'));
        router.push({ name: 'contents.index', query: { tab: 'templates' } });
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response: { status: number, data: { errors: Record<string, string[]> } } };
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
            }
        } else {
            toast.error.fromResponse(error);
        }
    } finally {
        saving.value = false;
    }
};
</script>
