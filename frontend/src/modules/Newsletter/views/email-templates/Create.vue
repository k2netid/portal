<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <PageHeader
      borderless
      :title="$t('newsletter.email_templates.form.createTitle')"
      :subtitle="$t('newsletter.email_templates.form.createSubtitle')"
    >
      <template #actions>
        <Button
          variant="outline"
          size="sm"
          class="h-10 inline-flex items-center gap-2"
          @click="router.push({ name: 'email-templates' })"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          {{ $t('common.actions.back') }}
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
          <ConsoleFormCard :padded="false">
            <template #header>
              <div class="flex justify-between items-center w-full">
                <span class="text-base font-semibold tracking-tight text-foreground">
                  {{ $t('newsletter.email_templates.form.content') }}
                </span>
                <div class="flex space-x-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="showVariables = !showVariables"
                  >
                    {{ showVariables ? $t('newsletter.email_templates.form.hideVariables') : $t('newsletter.email_templates.form.showVariables') }}
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="previewTemplate"
                  >
                    {{ $t('common.actions.preview') }}
                  </Button>
                </div>
              </div>
            </template>
            <div class="space-y-4">
              <div
                v-if="showVariables"
                class="p-4 bg-muted rounded-lg mb-4"
              >
                <h3 class="text-sm font-medium text-foreground mb-2">
                  {{ $t('newsletter.email_templates.form.availableVariables') }}:
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                  <div
                    v-for="variable in variables"
                    :key="variable"
                    class="flex items-center"
                  >
                    <code class="px-2 py-1 bg-background rounded border border-border">{{ variable }}</code>
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <Label>
                  {{ $t('newsletter.email_templates.form.subject') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                  v-model="form.subject"
                  required
                  :class="{ 'border-destructive focus-visible:ring-destructive': errors.subject }"
                  :placeholder="$t('newsletter.email_templates.form.subjectPlaceholder')"
                />
                <p
                  v-if="errors.subject"
                  class="text-sm text-destructive"
                >
                  {{ Array.isArray(errors.subject) ? errors.subject[0] : errors.subject }}
                </p>
              </div>

              <div class="space-y-2">
                <Label>
                  {{ $t('newsletter.email_templates.form.body') }} <span class="text-destructive">*</span>
                </Label>
                <Textarea
                  v-model="form.body"
                  rows="20"
                  required
                  class="font-mono text-sm"
                  :class="{ 'border-destructive focus-visible:ring-destructive': errors.body }"
                  :placeholder="$t('newsletter.email_templates.form.bodyPlaceholder')"
                />
                <p
                  v-if="errors.body"
                  class="text-sm text-destructive"
                >
                  {{ Array.isArray(errors.body) ? errors.body[0] : errors.body }}
                </p>
              </div>
            </div>
          </ConsoleFormCard>
        </div>

        <!-- Sidebar (Right) -->
        <div class="space-y-6">
          <ConsoleFormCard :title="$t('newsletter.email_templates.form.details')">
              <div class="space-y-2">
                <Label>
                  {{ $t('newsletter.email_templates.form.name') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                  v-model="form.name"
                  required
                  :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
                  :placeholder="$t('newsletter.email_templates.form.namePlaceholder')"
                />
                <p
                  v-if="errors.name"
                  class="text-sm text-destructive"
                >
                  {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
                </p>
              </div>

              <div class="space-y-2">
                <Label>
                  {{ $t('newsletter.email_templates.form.type') }}
                </Label>
                <Select v-model="form.type">
                  <SelectTrigger :aria-label="$t('newsletter.email_templates.form.category')">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="custom">
                      {{ $t('newsletter.email_templates.types.custom') }}
                    </SelectItem>
                    <SelectItem value="notification">
                      {{ $t('newsletter.email_templates.types.notification') }}
                    </SelectItem>
                    <SelectItem value="transactional">
                      {{ $t('newsletter.email_templates.types.transactional') }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
          </ConsoleFormCard>

          <div class="flex items-center gap-2">
            <div class="flex-1" />
            <Button
              variant="outline"
              type="button"
              @click="router.push({ name: 'email-templates' })"
            >
              {{ $t('common.actions.cancel') }}
            </Button>
            <Button
              type="submit"
              :disabled="saving || !isValid"
            >
              <Loader2
                v-if="saving"
                class="w-4 h-4 mr-2 animate-spin"
              />
              {{ saving ? $t('common.messages.loading.creating') : $t('common.actions.create') }}
            </Button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';
import { logger } from '@/shared/utils/logger';
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { emailTemplateSchema } from '@/shared/schemas';
import {
  ArrowLeft,
  Loader2,
} from 'lucide-vue-next';

import {
    Button,
    Input,
    Label,
    Textarea,
    Select,
    SelectTrigger,
    SelectValue,
    SelectContent,
    SelectItem
} from '@/shared/components/ui';

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(emailTemplateSchema);
const saving = ref(false);
const showVariables = ref(false);

const form = ref({
    name: '',
    subject: '',
    type: 'custom',
    body: '',
});

const isValid = computed(() => {
    return !!form.value.name?.trim() && 
           !!form.value.subject?.trim() && 
           !!form.value.body?.trim();
});

const variables = ref([
    'user_name',
    'user_email',
    'site_name',
    'site_url',
    'current_date',
    'current_time',
]);

const previewTemplate = async () => {
    try {
        const response = await api.post('/manage/system/email-templates/preview', form.value);
        const previewWindow = window.open('', '_blank');
        if (previewWindow) {
            previewWindow.document.write((response.data as any).body);
        }
    } catch (error: unknown) {
        logger.error('Failed to preview template:', error);
        toast.error.default(t('newsletter.email_templates.form.previewFailed'));
    }
};

const handleSubmit = async () => {
    // Map body to content for schema validation
    const validationData = { name: form.value.name, subject: form.value.subject, content: form.value.body };
    if (!validateWithZod(validationData)) return;

    saving.value = true;
    clearErrors();
    try {
        await api.post('/manage/system/email-templates', form.value);
        toast.success.create(t('newsletter.email_templates.list.title'));
        router.push({ name: 'email-templates' });
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
