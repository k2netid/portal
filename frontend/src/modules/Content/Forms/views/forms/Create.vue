<template>
  <div class="space-y-6 pb-20">
    <PageHeader
      :title="$t('forms.modal.createTitle')"
      :subtitle="$t('forms.title')"
      borderless
    >
      <template #actions>
        <div class="flex items-center gap-3">
          <router-link :to="{ name: 'forms' }">
            <Button
              variant="outline"
              size="sm"
              type="button"
            >
              {{ $t('common.actions.cancel') }}
            </Button>
          </router-link>
          <Button
            type="button"
            size="sm"
            variant="default"
            :disabled="saving || !isValid"
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
            {{ saving ? $t('common.messages.loading.creating') : $t('forms.actions.createForm') }}
          </Button>
        </div>
      </template>
    </PageHeader>

      <!-- Essential Meta Card -->
      <ConsoleFormCard class="mb-6">
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
              @input="generateSlug"
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

    <!-- Tabs Navigation -->
    <Tabs
      default-value="settings"
      class="w-full"
    >
      <div class="mb-10 flex items-center justify-between">
        <TabsList class="bg-transparent p-0 h-auto gap-0">
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
        value="settings"
        class="mt-0 focus-visible:ring-0 space-y-6"
      >
        <FormCrmLeadSettings v-model:settings="formData.settings" />
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                  {{ $t('forms.modal.publishingTips.slug') }}
                </li>
                <li class="flex items-start gap-2">
                  <div class="h-1 w-1 bg-primary rounded-full mt-1.5 shrink-0" />
                  {{ $t('forms.modal.publishingTips.fields') }}
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
</template>

<script setup lang="ts">
import { PageHeader, ConsoleFormCard } from '@/shared/components/shell';

import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { FormsService } from '@/modules/Content/Forms/services/formsService';
import { useToast } from '@/shared/composables/useToast';
import { useFormValidation } from '@/shared/composables/useFormValidation';
import FormCrmLeadSettings from '../../components/FormCrmLeadSettings.vue';
import { formSettingsSchema } from '@/shared/schemas';
import { Button, Checkbox, Input, Textarea, Tabs, TabsList, TabsTrigger, TabsContent } from '@/shared/components/ui';
import {
  Globe,
  Info,
  LinkIcon,
  Loader2,
  Save,
  Settings2,
} from 'lucide-vue-next';


const router = useRouter();
const { t } = useI18n();
const toast = useToast();
const { errors, validateWithZod, setErrors, clearErrors } = useFormValidation(formSettingsSchema);

const saving = ref(false);

const formData = reactive({
    name: '',
    slug: '',
    description: '',
    success_message: '',
    redirect_url: '',
    is_active: true,
        settings: {} as Record<string, unknown>
});

const isValid = computed(() => {
    return !!formData.name?.trim() && !!formData.slug?.trim();
});

const generateSlug = () => {
    if (!formData.slug || formData.slug === slugify(formData.name)) {
        formData.slug = slugify(formData.name);
    }
};

const slugify = (text: string) => {
    if (!text) return '';
    return text.toString().toLowerCase().trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w-\s]+/g, '')
        .replace(/-+/, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
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
            settings: formData.settings
        };
        const res = await FormsService.create(payload);
        const created = res.data as { id: string };
        toast.success.create(t('forms.title'));
        router.push({ name: 'forms.edit', params: { id: String(created.id) } });
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
