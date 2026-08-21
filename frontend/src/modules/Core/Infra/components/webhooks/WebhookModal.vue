<template>
  <Dialog
    :open="true"
    @update:open="$emit('close')"
  >
    <DialogContent class="console-dialog-md sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>
          {{ webhook ? t('infra.webhooks.modal.title_edit') : t('infra.webhooks.modal.title_create') }}
        </DialogTitle>
        <DialogDescription class="sr-only">
          {{ webhook ? t('infra.webhooks.modal.title_edit') : t('infra.webhooks.modal.title_create') }}
        </DialogDescription>
      </DialogHeader>

      <form
        class="p-6 space-y-4"
        @submit.prevent="handleSubmit"
      >
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ t('infra.webhooks.modal.name_label') }} <span class="text-red-500">*</span>
          </label>
          <Input
            v-model="form.name"
            type="text"
            required
            :placeholder="t('infra.webhooks.modal.name_placeholder')"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ t('infra.webhooks.modal.url_label') }} <span class="text-red-500">*</span>
          </label>
          <Input
            v-model="form.url"
            type="url"
            required
            class="font-mono"
            :placeholder="t('infra.webhooks.modal.url_placeholder')"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ t('infra.webhooks.modal.events_label') }} <span class="text-red-500">*</span>
          </label>
          <div class="space-y-2">
            <label
              v-for="event in availableEvents"
              :key="event"
              class="flex items-center"
            >
              <Checkbox
                :checked="form.events.includes(event)"
                @update:checked="(checked) => {
                  if (checked) form.events.push(event);
                  else form.events = form.events.filter(e => e !== event);
                }"
              />
              <span class="ml-2 text-sm text-foreground">{{ t('infra.webhooks.events.' + event) }}</span>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ t('infra.webhooks.modal.secret_label') }}
          </label>
          <Input
            v-model="form.secret"
            type="text"
            class="font-mono"
            :placeholder="t('infra.webhooks.modal.secret_placeholder')"
          />
        </div>

        <div class="flex items-center">
          <Checkbox
            id="is_active"
            :checked="form.is_active"
            @update:checked="(val) => form.is_active = val"
          />
          <label
            for="is_active"
            class="ml-2 block text-sm text-foreground"
          >
            {{ t('infra.webhooks.modal.active_label') }}
          </label>
        </div>
      </form>

      <DialogFooter>
        <Button
          variant="outline"
          size="sm" class="h-10" @click="$emit('close')"
        >
          {{ t('infra.webhooks.modal.cancel') }}
        </Button>
        <Button
          :disabled="saving || !isValid || (webhook && !isDirty)"
          @click="handleSubmit"
        >
          <Loader2
            v-if="saving"
            class="w-4 h-4 mr-2 animate-spin"
          />
          {{ saving ? t('infra.webhooks.modal.saving') : (webhook ? t('infra.webhooks.modal.update') : t('infra.webhooks.modal.create')) }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    Button,
    Input,
    Checkbox
} from '@/shared/components/ui';
import {
  Loader2,
} from 'lucide-vue-next';

interface Webhook {
    id: string;
    name: string;
    url: string;
    events: string[];
    secret?: string;
    is_active: boolean;
}

const { t } = useI18n();

const props = defineProps<{
    webhook?: Webhook | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

const saving = ref(false);

const availableEvents = [
    'content.created',
    'content.updated',
    'content.deleted',
    'user.created',
    'user.updated',
    'comment.created',
    'comment.approved',
];

interface WebhookForm {
    name: string;
    url: string;
    events: string[];
    secret: string;
    is_active: boolean;
}

const form = ref<WebhookForm>({
    name: '',
    url: '',
    events: [],
    secret: '',
    is_active: true,
});

const initialForm = ref<WebhookForm | null>(null);

const isValid = computed(() => {
    return !!form.value.name?.trim() && !!form.value.url?.trim() && form.value.events.length > 0;
});

const isDirty = computed(() => {
    if (!initialForm.value) return true;
    return JSON.stringify(form.value) !== JSON.stringify(initialForm.value);
});

const loadWebhook = () => {
    if (props.webhook) {
        form.value = {
            name: props.webhook.name || '',
            url: props.webhook.url || '',
            events: props.webhook.events || [],
            secret: props.webhook.secret || '',
            is_active: props.webhook.is_active !== undefined ? props.webhook.is_active : true,
        };
    } else {
        form.value = {
            name: '',
            url: '',
            events: [],
            secret: '',
            is_active: true,
        };
    }
    initialForm.value = JSON.parse(JSON.stringify(form.value));
};

const toast = useToast();

const handleSubmit = async () => {
    saving.value = true;
    try {
        if (props.webhook) {
            await api.put(`/manage/infra/webhooks/${props.webhook.id}`, form.value);
            toast.success.update('Webhook');
        } else {
            await api.post('/manage/infra/webhooks', form.value);
            toast.success.create('Webhook');
        }
        emit('saved');
    } catch (error: unknown) {
        logger.error('Failed to save webhook:', error);
        toast.error.fromResponse(error);
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    loadWebhook();
});
</script>

