<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-medium">{{ $t('system.profile.passkeys.title', 'Passkeys') }}</h3>
        <p class="text-sm text-muted-foreground">
          {{ $t('system.profile.passkeys.description', 'Use your fingerprint, face, or screen lock to sign in without a password.') }}
        </p>
      </div>
      <Button @click="openRegisterDialog" :disabled="isRegistering">
        <Loader2 v-if="isRegistering" class="mr-2 h-4 w-4 animate-spin" />
        <Plus v-else class="mr-2 h-4 w-4" />
        {{ $t('system.profile.passkeys.add', 'Add Passkey') }}
      </Button>
    </div>

    <Separator />

    <div v-if="isLoading" class="flex justify-center py-8">
      <Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
    </div>

    <div v-else-if="passkeys.length === 0" class="flex flex-col items-center justify-center py-12 text-center border rounded-lg bg-gray-50/50 border-dashed">
      <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center mb-4">
        <Fingerprint class="h-6 w-6 text-primary" />
      </div>
      <h3 class="font-medium mb-1">{{ $t('system.profile.passkeys.empty.title', 'No passkeys found') }}</h3>
      <p class="text-sm text-muted-foreground max-w-sm mb-4">
        {{ $t('system.profile.passkeys.empty.description', 'You have not registered any passkeys yet. Add one to sign in securely without a password.') }}
      </p>
      <Button variant="outline" @click="openRegisterDialog" :disabled="isRegistering">
        {{ $t('system.profile.passkeys.add', 'Add Passkey') }}
      </Button>
    </div>

    <div v-else class="space-y-4">
      <Card v-for="passkey in passkeys" :key="passkey.id">
        <CardContent class="p-4 flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
              <Fingerprint class="h-5 w-5 text-primary" />
            </div>
            <div>
              <p class="font-medium">{{ passkey.name }}</p>
              <p class="text-xs text-muted-foreground">
                {{ $t('system.profile.passkeys.addedOn', 'Added on') }} {{ formatDate(passkey.created_at) }}
              </p>
            </div>
          </div>
          <Button variant="destructive" size="sm" @click="askRemove(passkey)">
            <Trash2 class="h-4 w-4 mr-2" />
            {{ $t('common.actions.remove', 'Remove') }}
          </Button>
        </CardContent>
      </Card>
    </div>

    <Dialog v-model:open="registerOpen">
      <DialogContent class="console-dialog-md sm:max-w-md bg-card border border-border/80 rounded-xl">
        <DialogHeader>
          <DialogTitle>{{ $t('system.profile.passkeys.registerDialog.title', 'Add passkey') }}</DialogTitle>
          <DialogDescription>{{ $t('system.profile.passkeys.registerDialog.description', 'Choose a name you will recognize on this device.') }}</DialogDescription>
        </DialogHeader>
        <div class="grid gap-2 py-2">
          <Label for="passkey-name">{{ $t('system.profile.passkeys.registerDialog.label', 'Display name') }}</Label>
          <Input id="passkey-name" v-model="registerName" :placeholder="$t('system.profile.passkeys.registerDialog.placeholder', 'My laptop')" @keyup.enter="registerPasskey" />
        </div>
        <DialogFooter>
          <Button variant="outline" @click="registerOpen = false">{{ $t('common.actions.cancel', 'Cancel') }}</Button>
          <Button :disabled="!registerName.trim() || isRegistering" @click="registerPasskey">
            <Loader2 v-if="isRegistering" class="mr-2 h-4 w-4 animate-spin" />
            {{ $t('system.profile.passkeys.registerDialog.submit', 'Register') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { Passkeys } from '@laravel/passkeys';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { useConfirm } from '@/shared/composables/useConfirm';
import {
  Button, Card, CardContent, Separator, Input, Label,
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter,
} from '@/shared/components/ui';
import { Loader2, Plus, Fingerprint, Trash2 } from 'lucide-vue-next';
import { isAxiosError } from 'axios';

const { t } = useI18n();
const toast = useToast();
const { confirm } = useConfirm();

interface Passkey {
  id: number;
  name: string;
  created_at: string;
}

const passkeys = ref<Passkey[]>([]);
const isLoading = ref(true);
const isRegistering = ref(false);
const registerOpen = ref(false);
const registerName = ref('My Passkey');

const formatDate = (dateString: string) => {
  return new Intl.DateTimeFormat(undefined, {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  }).format(new Date(dateString));
};

const fetchPasskeys = async () => {
  isLoading.value = true;
  try {
    const response = await api.get('/manage/system/profile/passkeys');
    const data = response.data;
    passkeys.value = Array.isArray(data) ? data : [];
  } catch (error) {
    toast.error.load(error);
  } finally {
    isLoading.value = false;
  }
};

const openRegisterDialog = () => {
  registerName.value = t('system.profile.passkeys.registerDialog.defaultName', 'My Passkey');
  registerOpen.value = true;
};

const registerPasskey = async () => {
  const name = registerName.value.trim();
  if (!name) return;

  isRegistering.value = true;
  try {
    await Passkeys.register({
      name,
      routes: {
        submit: '/user/passkeys',
        options: '/user/passkeys/options',
      },
    });
    toast.success.default(t('system.profile.passkeys.registerSuccess', 'Passkey added successfully'));
    registerOpen.value = false;
    await fetchPasskeys();
  } catch (error) {
    let msg = t('system.profile.passkeys.registerError', 'Failed to add passkey');
    if (isAxiosError(error)) {
      msg = error.response?.data?.message || msg;
    }
    toast.error.default(msg);
  } finally {
    isRegistering.value = false;
  }
};

const askRemove = async (passkey: Passkey) => {
  const ok = await confirm({
    title: t('system.profile.passkeys.removeDialog.title', 'Remove passkey?'),
    message: t('system.profile.passkeys.confirmRemove', { name: passkey.name }),
    confirmText: t('common.actions.remove', 'Remove'),
    variant: 'destructive',
  });
  if (!ok) return;

  try {
    await api.delete(`/user/passkeys/${passkey.id}`);
    toast.success.default(t('system.profile.passkeys.removeSuccess', 'Passkey removed successfully'));
    await fetchPasskeys();
  } catch (error) {
    toast.error.default(t('system.profile.passkeys.removeError', 'Failed to remove passkey'));
  }
};

onMounted(fetchPasskeys);
</script>
