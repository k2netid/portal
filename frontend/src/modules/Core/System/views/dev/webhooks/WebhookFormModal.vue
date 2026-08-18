<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="console-dialog-md sm:max-w-lg bg-card border border-border/80 rounded-xl">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? t('system.webhooks.form.editTitle') : t('system.webhooks.form.createTitle') }}</DialogTitle>
        <DialogDescription>{{ t('system.webhooks.form.description') }}</DialogDescription>
      </DialogHeader>

      <form class="space-y-4 mt-2" @submit.prevent="submit">
        <div class="space-y-2">
          <Label for="webhook-name">{{ t('system.webhooks.form.name') }}</Label>
          <Input id="webhook-name" v-model="form.name" required />
        </div>
        <div class="space-y-2">
          <Label for="webhook-url">{{ t('system.webhooks.form.url') }}</Label>
          <Input id="webhook-url" v-model="form.url" type="url" required placeholder="https://example.com/hooks/identity" />
        </div>
        <div class="space-y-2">
          <Label for="webhook-secret">{{ t('system.webhooks.form.secret') }}</Label>
          <Input id="webhook-secret" v-model="form.secret" type="password" autocomplete="new-password" />
        </div>
        <div class="space-y-2">
          <Label>{{ t('system.webhooks.form.events') }}</Label>
          <div class="flex flex-wrap gap-2">
            <label v-for="ev in eventOptions" :key="ev" class="flex items-center gap-1.5 text-sm">
              <Checkbox :checked="form.events.includes(ev)" @update:checked="(v) => toggleEvent(ev, v === true)" />
              <span>{{ ev }}</span>
            </label>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Checkbox id="webhook-active" :checked="form.is_active" @update:checked="form.is_active = $event === true" />
          <Label for="webhook-active" class="font-normal cursor-pointer">{{ t('system.webhooks.form.active') }}</Label>
        </div>
        <p v-if="error" class="text-sm text-destructive">{{ error }}</p>
        <DialogFooter class="gap-2">
          <Button type="button" variant="secondary" @click="$emit('update:open', false)">{{ t('common.actions.cancel') }}</Button>
          <Button type="submit" :disabled="saving">{{ isEdit ? t('common.actions.save') : t('common.actions.create') }}</Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button, Checkbox, Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, Input, Label } from '@/shared/components/ui';
import api from '@/engine/api/client';

export interface WebhookRow {
  id: string;
  name: string;
  url: string;
  events: string[];
  secret?: string;
  is_active: boolean;
}

const props = defineProps<{ open: boolean; webhook?: WebhookRow | null }>();
const emit = defineEmits<{ (e: 'update:open', val: boolean): void; (e: 'saved'): void }>();
const { t } = useI18n();

const eventOptions = ['*', 'user.created', 'user.updated', 'user.deleted', 'user.password_changed', 'member.created', 'member.updated', 'member.deleted'];
const saving = ref(false);
const error = ref('');
const isEdit = computed(() => Boolean(props.webhook?.id));

const form = reactive({ name: '', url: '', secret: '', events: [] as string[], is_active: true });

function toggleEvent(ev: string, on: boolean) {
  if (on && !form.events.includes(ev)) form.events.push(ev);
  if (!on) form.events = form.events.filter((e) => e !== ev);
}

watch(() => [props.open, props.webhook] as const, () => {
  if (!props.open) return;
  error.value = '';
  form.name = props.webhook?.name ?? '';
  form.url = props.webhook?.url ?? '';
  form.secret = '';
  form.events = [...(props.webhook?.events ?? ['user.updated'])];
  form.is_active = props.webhook?.is_active ?? true;
});

async function submit() {
  if (form.events.length === 0) {
    error.value = t('system.webhooks.form.eventsRequired');
    return;
  }
  saving.value = true;
  error.value = '';
  const payload = { name: form.name, url: form.url, events: form.events, is_active: form.is_active, ...(form.secret ? { secret: form.secret } : {}) };
  try {
    if (isEdit.value && props.webhook) {
      await api.put(`/manage/infra/webhooks/${props.webhook.id}`, payload);
    } else {
      await api.post('/manage/infra/webhooks', payload);
    }
    emit('update:open', false);
    emit('saved');
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } }; message?: string };
    error.value = err.response?.data?.message ?? err.message ?? t('common.messages.error.generic');
  } finally {
    saving.value = false;
  }
}
</script>
