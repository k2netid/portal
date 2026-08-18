<template>
  <div class="space-y-6 pb-20">
    <PageHeader
      :title="$t('forms.modal.editTitle')"
      :subtitle="$t('forms.title')"
      borderless
    >
      <template #actions>
        <div class="flex items-center gap-3">
          <router-link
            :to="{ name: 'forms' }"
            class="inline-flex h-8 items-center justify-center rounded-lg border border-border/60 bg-background px-2.5 text-sm font-medium hover:bg-accent/50"
          >
            {{ $t('common.actions.cancel') }}
          </router-link>
          <Button
            type="button"
            size="sm"
            variant="default"
            :disabled="saving || !isDirty"
            @click="handleSubmit"
          >
            <Loader2
              v-if="saving"
              class="animate-spin mr-2 h-4 w-4"
            />
            <Save
              v-else
              class="mr-2 h-4 w-4"
            />
            {{ saving ? $t('common.messages.loading.saving') : $t('forms.actions.update') }}
          </Button>
        </div>
      </template>
    </PageHeader>

      <ConsoleFormCard
        v-if="loading"
        :padded="false"
        class="p-12 text-center animate-pulse"
      >
        <Loader2 class="h-8 w-8 animate-spin mx-auto text-primary mb-2" />
        <p class="text-muted-foreground">
          {{ $t('common.messages.loading.default') }}
        </p>
      </ConsoleFormCard>

      <!-- Essential Meta Card -->
      <ConsoleFormCard
        v-else
        class="mb-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-4 items-end gap-6">
          <div class="md:col-span-2">
            <label class="block text-[11px] uppercase tracking-wider font-bold text-muted-foreground mb-1.5 ml-0.5">
              {{ $t('forms.modal.formName') }} <span class="text-destructive font-normal">*</span>
            </label>
            <Input
              v-model="formData.name"
              type="text"
              required
              class="bg-background/50 border-border focus:ring-primary/20 h-11"
              :placeholder="$t('forms.modal.placeholders.name')"
              :class="{ 'border-destructive focus-visible:ring-destructive': errors.name }"
            />
            <p
              v-if="errors.name"
              class="text-xs text-destructive mt-1 ml-0.5"
            >
              {{ Array.isArray(errors.name) ? errors.name[0] : errors.name }}
            </p>
          </div>

          <div>
            <label class="block text-[11px] uppercase tracking-wider font-bold text-muted-foreground mb-1.5 ml-0.5">
              {{ $t('forms.modal.slug') }} <span class="text-destructive font-normal">*</span>
            </label>
            <div class="relative">
              <Input
                v-model="formData.slug"
                type="text"
                required
                class="bg-background/50 border-border focus:ring-primary/20 pl-7 h-11"
                :placeholder="$t('forms.modal.placeholders.slug')"
                :class="{ 'border-destructive focus-visible:ring-destructive': errors.slug }"
              />
              <LinkIcon class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
            </div>
            <p
              v-if="errors.slug"
              class="text-xs text-destructive mt-1 ml-0.5"
            >
              {{ Array.isArray(errors.slug) ? errors.slug[0] : errors.slug }}
            </p>
          </div>

          <div class="flex items-center space-x-2 h-11 pb-2">
            <Checkbox
              id="is_active"
              v-model:checked="formData.is_active"
              :aria-label="$t('forms.modal.isActive')"
            />
            <label
              for="is_active"
              class="text-sm font-medium leading-none cursor-pointer"
            >
              {{ $t('forms.modal.isActive') }}
            </label>
          </div>
        </div>
      </ConsoleFormCard>

    <div v-if="!loading">
      <!-- Tabs Navigation -->
      <Tabs
        default-value="designer"
        class="w-full"
      >
        <div class="mb-10 flex items-center justify-between">
          <TabsList class="bg-transparent p-0 h-auto gap-0">
            <TabsTrigger
              value="designer"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
            >
              <LayoutList class="h-4 w-4 mr-2" />
              {{ $t('forms.modal.tabs.fields') }}
            </TabsTrigger>
            <TabsTrigger
              value="settings"
              class="relative px-6 py-3 data-[state=active]:bg-transparent data-[state=active]:shadow-none data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none transition-colors"
            >
              <Settings2 class="h-4 w-4 mr-2" />
              {{ $t('forms.modal.tabs.settings') }}
            </TabsTrigger>
          </TabsList>
        </div>

        <TabsContent
          value="designer"
          class="mt-0 focus-visible:ring-0"
        >
          <FormBuilderDesigner :form-id="String(route.params.id)" />
        </TabsContent>

        <TabsContent
          value="settings"
          class="mt-0 focus-visible:ring-0"
        >
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<FormCrmLeadSettings
                v-model:settings="formData.settings"
                :form-fields="formFields"
                class="lg:col-span-2"
              />

            <ConsoleFormCard class="lg:col-span-2 p-6 space-y-8" :padded="false">
              <div class="space-y-4">
                <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                  {{ $t('forms.modal.description') }}
                </h3>
                <div>
                  <Textarea
                    v-model="formData.description"
                    rows="4"
                    class="bg-background/50 border-border focus:ring-primary/20"
                    :placeholder="$t('forms.modal.placeholders.description')"
                  />
                  <p class="text-xs text-muted-foreground mt-2 italic">
                    {{ $t('forms.modal.publishingTips.descriptionHelper') }}
                  </p>
                </div>
              </div>

              <div class="space-y-6 pt-2">
                <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                  {{ $t('forms.modal.submissionBehavior') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-foreground">
                      {{ $t('forms.modal.successMessage') }}
                    </label>
                    <Input
                      v-model="formData.success_message"
                      type="text"
                      class="bg-background/50 border-border focus:ring-primary/20"
                      :placeholder="$t('forms.modal.placeholders.successMessage')"
                    />
                    <p class="text-[11px] text-muted-foreground italic">
                      {{ $t('forms.modal.publishingTips.successMessageHelper') }}
                    </p>
                  </div>
                
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-foreground">
                      {{ $t('forms.modal.redirectUrl') }}
                    </label>
                    <div class="relative">
                      <Input
                        v-model="formData.redirect_url"
                        type="url"
                        class="bg-background/50 border-border focus:ring-primary/20 pl-8"
                        :placeholder="$t('forms.modal.placeholders.redirectUrl')"
                      />
                      <Globe class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground" />
                    </div>
                    <p class="text-[11px] text-muted-foreground italic">
                      {{ $t('forms.modal.publishingTips.redirectUrlHelper') }}
                    </p>
                  </div>
                </div>
              </div>
            </ConsoleFormCard>

            <div class="space-y-6">
              <ConsoleFormCard class="p-6 h-fit space-y-4" :padded="false">
                <h3 class="font-bold flex items-center text-primary">
                  <Info class="h-4 w-4 mr-2" />
                  {{ $t('forms.modal.publishingTips.title') }}
                </h3>
                <ul class="text-xs space-y-3 text-muted-foreground leading-relaxed">
                  <li class="flex items-start gap-2">
                    <div class="h-1 w-1 bg-primary rounded-full mt-1.5 shrink-0" />
                    {{ $t('forms.modal.publishingTips.edit') }}
                  </li>
                  <li class="flex items-start gap-2">
                    <div class="h-1 w-1 bg-primary rounded-full mt-1.5 shrink-0" />
                    {{ $t('forms.modal.publishingTips.mobile') }}
                  </li>
                </ul>
              </ConsoleFormCard>
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';

import { logger } from '@/shared/utils/logger';
import { ref, reactive, onMounted, computed } from 'vue';

import { useRouter, useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { FormsService } from '@/modules/Content/Forms/services/formsService';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import { formSettingsSchema } from '@/shared/schemas';
import { Button, Checkbox, Input, Textarea, Tabs, TabsList, TabsTrigger, TabsContent } from '@/shared/components/ui';
import {
  Globe,
  Info,
  LayoutList,
  LinkIcon,
  Loader2,
  Save,
  Settings2,
} from 'lucide-vue-next';
import FormBuilderDesigner from './components/FormBuilderDesigner.vue';
import FormCrmLeadSettings from '../../components/FormCrmLeadSettings.vue';
import type { FormField } from '../../types/forms';


const router = useRouter();
const route = useRoute();
const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(formSettingsSchema);

const loading = ref(true);
const saving = ref(false);

const formFields = ref<FormField[]>([]);

const formData = reactive({
    name: '',
    slug: '',
    description: '',
    success_message: '',
    redirect_url: '',
    is_active: true,
    settings: {} as Record<string, unknown>,
});

const initialForm = ref<Record<string, unknown> | null>(null);

const isDirty = computed(() => {
    if (!initialForm.value) return false;
    return JSON.stringify(formData) !== JSON.stringify(initialForm.value);
});

const fetchForm = async () => {
    loading.value = true;
    try {
        const response = await FormsService.get(String(route.params.id));
        const data = response.data;
        Object.assign(formData, {
            name: data.name,
            slug: data.slug,
            description: data.description,
            success_message: data.success_message,
            redirect_url: data.redirect_url,
            is_active: data.is_active,
            settings: (data.settings && typeof data.settings === 'object') ? { ...data.settings } : {},
        });
        formFields.value = Array.isArray(data.fields) ? data.fields : [];
        
        initialForm.value = JSON.parse(JSON.stringify(formData));
    } catch (error: unknown) {
        logger.error('Failed to fetch form:', error);
        toast.error.load(error);
        router.push({ name: 'forms' });
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    const validationData = { name: formData.name, slug: formData.slug };
    if (!validateWithZod(validationData)) return;

    saving.value = true;
    clearErrors();
    try {
        const payload = {
            name: formData.name,
            slug: formData.slug,
            description: formData.description,
            success_message: formData.success_message,
            redirect_url: formData.redirect_url,
            is_active: formData.is_active,
            settings: formData.settings,
        };
        await FormsService.update(String(route.params.id), payload);
        initialForm.value = JSON.parse(JSON.stringify(formData));
        toast.success.update(t('forms.title'));
        router.push({ name: 'forms' });
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

onMounted(() => {
    fetchForm();
});
</script>
