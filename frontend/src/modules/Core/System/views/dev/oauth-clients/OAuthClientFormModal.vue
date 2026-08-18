<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="console-dialog-md sm:max-w-lg bg-card border border-border/80 rounded-xl">
      <DialogHeader>
        <DialogTitle>{{ isEdit ? t('system.oauth.form.editTitle') : t('system.oauth.form.createTitle') }}</DialogTitle>
        <DialogDescription>{{ t('system.oauth.form.description') }}</DialogDescription>
      </DialogHeader>

      <form class="space-y-4 mt-2" @submit.prevent="submit">
        <div class="space-y-2">
          <Label for="oauth-client-name">{{ t('system.oauth.form.name') }}</Label>
          <Input id="oauth-client-name" v-model="form.name" required autocomplete="off" />
        </div>
        <div class="space-y-2">
          <Label for="oauth-client-redirect">{{ t('system.oauth.form.redirect') }}</Label>
          <Input
            id="oauth-client-redirect"
            v-model="form.redirect"
            type="url"
            required
            placeholder="https://app.example.com/oauth/callback"
          />
        </div>
        <div v-if="!isEdit" class="flex items-center gap-2">
          <Checkbox
            id="oauth-client-confidential"
            :checked="form.confidential"
            @update:checked="form.confidential = $event === true"
          />
          <Label for="oauth-client-confidential" class="font-normal cursor-pointer">
            {{ t('system.oauth.form.confidential') }}
          </Label>
        </div>
        <div
          v-if="createdSecret"
          class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-3 text-sm"
        >
          <p class="font-semibold text-amber-700 dark:text-amber-400">{{ t('system.oauth.form.secretTitle') }}</p>
          <p class="mt-1 font-mono text-xs break-all">{{ createdSecret }}</p>
          <p class="mt-2 text-muted-foreground text-xs">{{ t('system.oauth.form.secretHint') }}</p>
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
import {
  Button, Checkbox, Dialog, DialogContent, DialogDescription, DialogFooter,
  DialogHeader, DialogTitle, Input, Label,
} from '@/shared/components/ui';
import api from '@/engine/api/client';

export interface OAuthClient {
  id: string;
  name: string;
  redirect: string;
  secret?: string;
  personal_access_client: boolean;
  password_client: boolean;
  revoked: boolean;
  created_at: string;
}

const props = defineProps<{ open: boolean; client?: OAuthClient | null }>();
const emit = defineEmits<{ (e: 'update:open', val: boolean): void; (e: 'saved'): void }>();
const { t } = useI18n();
const saving = ref(false);
const error = ref('');
const createdSecret = ref('');
const form = reactive({ name: '', redirect: '', confidential: true });
const isEdit = computed(() => Boolean(props.client?.id));

watch(() => [props.open, props.client] as const, () => {
  if (!props.open) return;
  error.value = '';
  createdSecret.value = '';
  form.name = props.client?.name ?? '';
  form.redirect = props.client?.redirect ?? '';
  form.confidential = true;
});

async function submit() {
  saving.value = true;
  error.value = '';
  createdSecret.value = '';
  try {
    if (isEdit.value && props.client) {
      await api.put(`/manage/system/oauth-clients/${props.client.id}`, { name: form.name, redirect: form.redirect });
      emit('update:open', false);
      emit('saved');
    } else {
      const created = (await api.post('/manage/system/oauth-clients', {
        name: form.name, redirect: form.redirect, confidential: form.confidential,
      })) as { secret?: string };
      if (created.secret) createdSecret.value = String(created.secret);
      emit('saved');
      if (!createdSecret.value) emit('update:open', false);
    }
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } }; message?: string };
    error.value = err.response?.data?.message ?? err.message ?? t('common.messages.error.generic');
  } finally {
    saving.value = false;
  }
}
</script>
