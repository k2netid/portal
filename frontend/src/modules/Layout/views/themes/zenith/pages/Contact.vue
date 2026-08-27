<template>
  <div class="zenith-theme flex-1 flex flex-col py-12 sm:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 w-full">
      <div class="text-center space-y-4">
        <h1 class="text-4xl sm:text-5xl font-extrabold text-foreground font-heading">
          {{ t('theme.zenith.pages.contact.title', 'Get in Touch') }}
        </h1>
        <p class="text-lg text-muted-foreground">
          {{ formDescription || t('theme.zenith.pages.contact.subtitle', "We'd love to hear from you. Reach out with any inquiries.") }}
        </p>
      </div>

      <Card class="space-y-6">
        <p
          v-if="loadError"
          class="text-sm text-muted-foreground"
        >
          {{ loadError }}
        </p>

        <form
          class="space-y-5"
          @submit.prevent="handleSubmit"
        >
          <div
            v-for="field in fields"
            :key="field.name"
            class="space-y-2"
          >
            <label class="text-sm font-semibold text-foreground">
              {{ field.label }}
            </label>
            <textarea
              v-if="field.type === 'textarea'"
              v-model="values[field.name]"
              rows="5"
              :required="field.is_required ?? field.is_required"
              :placeholder="field.placeholder || ''"
              class="w-full px-4 py-2.5 rounded-xl border border-border/80 bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/50 resize-none"
            />
            <input
              v-else
              v-model="values[field.name]"
              :type="inputType(field.type)"
              :required="field.is_required ?? field.is_required"
              :placeholder="field.placeholder || ''"
              class="w-full px-4 py-2.5 rounded-xl border border-border/80 bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary/50"
            >
          </div>

          <p
            v-if="submitError"
            class="text-sm text-destructive"
          >
            {{ submitError }}
          </p>

          <Button
            type="submit"
            size="lg"
            variant="primary"
            class="w-full justify-center"
            :disabled="submitting || fields.length === 0"
          >
            {{ submitted
              ? (successMessage || t('theme.zenith.pages.contact.sent', 'Message Sent!'))
              : t('theme.zenith.pages.contact.submitButton', 'Send Message') }}
          </Button>
        </form>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { Card, Button } from '@/modules/Layout/views/themes/zenith/ui';

interface PublicField {
    name: string;
    label: string;
    type: string;
    placeholder?: string | null;
    is_required?: boolean;
}

const CONTACT_SLUG = 'contact';

const { t } = useI18n();

const fields = ref<PublicField[]>([]);
const values = reactive<Record<string, string>>({});
const formDescription = ref('');
const successMessage = ref('');
const loadError = ref('');
const submitError = ref('');
const submitting = ref(false);
const submitted = ref(false);

const inputType = (type: string): string => {
    if (type === 'email') return 'email';
    if (type === 'tel' || type === 'phone') return 'tel';
    return 'text';
};

const fallbackFields = (): PublicField[] => ([
    { name: 'name', label: t('theme.zenith.pages.contact.nameLabel', 'Your Name'), type: 'text', is_required: true },
    { name: 'email', label: t('theme.zenith.pages.contact.emailLabel', 'Email Address'), type: 'email', is_required: true },
    { name: 'message', label: t('theme.zenith.pages.contact.messageLabel', 'Message'), type: 'textarea', is_required: true },
]);

onMounted(async () => {
    try {
        const res = await api.get(`/public/forms/${CONTACT_SLUG}`);
        const payload = res.data as { fields?: PublicField[]; description?: string; success_message?: string };
        const incoming = Array.isArray(payload.fields) ? payload.fields : [];
        fields.value = incoming.length > 0 ? incoming : fallbackFields();
        formDescription.value = payload.description || '';
        successMessage.value = payload.success_message || '';
        for (const field of fields.value) {
            values[field.name] = '';
        }
        void api.post(`/public/forms/${CONTACT_SLUG}/track`, { event: 'view' }).catch(() => undefined);
    } catch {
        fields.value = fallbackFields();
        for (const field of fields.value) {
            values[field.name] = '';
        }
        loadError.value = t(
            'theme.zenith.pages.contact.formsHint',
            'Activate the Forms pack and publish a form with slug “contact” to store submissions.',
        );
    }
});

const handleSubmit = async (): Promise<void> => {
    submitError.value = '';
    submitting.value = true;
    try {
        await api.post(`/public/forms/${CONTACT_SLUG}/submit`, { ...values });
        submitted.value = true;
        for (const key of Object.keys(values)) {
            values[key] = '';
        }
        setTimeout(() => {
            submitted.value = false;
        }, 3000);
    } catch {
        submitError.value = t(
            'theme.zenith.pages.contact.submitFailed',
            'Could not send this message. Check that the Forms pack is active.',
        );
    } finally {
        submitting.value = false;
    }
};
</script>
