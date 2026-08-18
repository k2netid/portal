<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-medium">{{ t('system.kyc.title') }}</h3>
        <p class="text-sm text-muted-foreground">{{ t('system.kyc.subtitle') }}</p>
      </div>
      <Badge :variant="kycLevel === 'level_3' ? 'default' : 'secondary'">{{ currentLevelName }}</Badge>
    </div>

    <Alert v-if="submission?.status === 'pending_review'" variant="default">
      <Clock class="h-4 w-4" />
      <AlertTitle>{{ t('system.kyc.pending.title') }}</AlertTitle>
      <AlertDescription>{{ t('system.kyc.pending.description') }}</AlertDescription>
    </Alert>

    <Alert v-if="submission?.status === 'rejected'" variant="destructive">
      <AlertCircle class="h-4 w-4" />
      <AlertTitle>{{ t('system.kyc.rejected.title') }}</AlertTitle>
      <AlertDescription>{{ submission.rejection_reason || t('system.kyc.rejected.description') }}</AlertDescription>
    </Alert>

    <Separator />

    <!-- Step 1 -->
    <section class="space-y-4">
      <h4 class="font-medium flex items-center gap-2">
        <CheckCircle2 v-if="levelRank >= 1" class="h-4 w-4 text-primary" />
        {{ t('system.kyc.steps.basic.title') }}
      </h4>
      <p class="text-sm text-muted-foreground">{{ t('system.kyc.steps.basic.desc') }}</p>
      <div v-if="levelRank < 1" class="grid gap-3 max-w-md">
        <div class="grid gap-2">
          <Label>{{ t('system.kyc.form.name') }}</Label>
          <Input v-model="basicForm.name" />
        </div>
        <div class="grid gap-2">
          <Label>{{ t('system.kyc.form.phone') }}</Label>
          <Input v-model="basicForm.phone" />
        </div>
        <div class="grid gap-2">
          <Label>{{ t('system.kyc.form.location') }}</Label>
          <Input v-model="basicForm.location" />
        </div>
        <Button :disabled="saving" @click="submitBasic">
          <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
          {{ t('system.kyc.actions.completeBasic') }}
        </Button>
      </div>
      <p v-else class="text-sm text-green-600 dark:text-green-400">{{ t('system.kyc.steps.basic.done') }}</p>
    </section>

    <Separator />

    <!-- Step 2 -->
    <section class="space-y-4">
      <h4 class="font-medium flex items-center gap-2">
        <CheckCircle2 v-if="levelRank >= 2" class="h-4 w-4 text-primary" />
        {{ t('system.kyc.steps.contact.title') }}
      </h4>
      <p class="text-sm text-muted-foreground">{{ t('system.kyc.steps.contact.desc') }}</p>
      <ul class="text-sm space-y-1 text-muted-foreground">
        <li>{{ emailVerified ? '✓' : '○' }} {{ t('system.kyc.requirements.emailVerified') }}</li>
        <li>{{ hasPhone ? '✓' : '○' }} {{ t('system.kyc.requirements.phone') }}</li>
      </ul>
      <Button v-if="levelRank >= 1 && levelRank < 2" :disabled="saving || !emailVerified || !hasPhone" @click="submitContact">
        <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
        {{ t('system.kyc.actions.completeContact') }}
      </Button>
      <p v-else-if="levelRank >= 2" class="text-sm text-green-600 dark:text-green-400">{{ t('system.kyc.steps.contact.done') }}</p>
    </section>

    <Separator />

    <!-- Step 3 -->
    <section class="space-y-4">
      <h4 class="font-medium flex items-center gap-2">
        <CheckCircle2 v-if="levelRank >= 3" class="h-4 w-4 text-primary" />
        {{ t('system.kyc.steps.identity.title') }}
      </h4>
      <p class="text-sm text-muted-foreground">{{ t('system.kyc.steps.identity.desc') }}</p>

      <div v-if="levelRank >= 2 && canEditDocuments" class="space-y-4 max-w-lg">
        <div v-for="docType in documentTypes" :key="docType.key" class="rounded-lg border p-4 space-y-2">
          <Label>{{ docType.label }}</Label>
          <p class="text-xs text-muted-foreground">{{ docType.hint }}</p>
          <input type="file" accept="image/jpeg,image/png,application/pdf" class="text-sm" @change="onFile(docType.key, $event)" />
          <p v-if="docByType(docType.key)" class="text-xs text-muted-foreground">
            {{ docByType(docType.key)?.original_name }}
          </p>
        </div>
        <Button :disabled="saving || !hasIdentityDoc" @click="submitReview">
          <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
          {{ t('system.kyc.actions.submitReview') }}
        </Button>
      </div>
      <p v-else-if="levelRank >= 3" class="text-sm text-green-600 dark:text-green-400">{{ t('system.kyc.steps.identity.done') }}</p>
      <p v-else-if="levelRank < 2" class="text-sm text-muted-foreground">{{ t('system.kyc.steps.identity.locked') }}</p>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/modules/Core/System/stores/auth';
import api from '@/engine/api/client';
import { useToast } from '@/shared/composables/useToast';
import { Button, Input, Label, Separator, Badge, Alert, AlertTitle, AlertDescription } from '@/shared/components/ui';
import { Loader2, CheckCircle2, Clock, AlertCircle } from 'lucide-vue-next';

const { t } = useI18n();
const toast = useToast();
const authStore = useAuthStore();

interface KycDoc { id: string; type: string; original_name: string }
interface KycSubmission {
  id: string;
  status: string;
  rejection_reason?: string | null;
  documents?: KycDoc[];
}

const kycLevel = ref('level_0');
const onboardingStep = ref(0);
const emailVerified = ref(false);
const hasPhone = ref(false);
const submission = ref<KycSubmission | null>(null);
const saving = ref(false);

const basicForm = ref({ name: '', phone: '', location: '' });

const levelRank = computed(() => {
  const m: Record<string, number> = { level_0: 0, level_1: 1, level_2: 2, level_3: 3 };
  return m[kycLevel.value] ?? 0;
});

const currentLevelName = computed(() => t(`system.kyc.levels.${kycLevel.value}`, kycLevel.value));

const documentTypes = computed(() => [
  { key: 'id_card', label: t('system.kyc.documents.idCard'), hint: t('system.kyc.documents.idCardHint') },
  { key: 'passport', label: t('system.kyc.documents.passport'), hint: t('system.kyc.documents.passportHint') },
  { key: 'selfie', label: t('system.kyc.documents.selfie'), hint: t('system.kyc.documents.selfieHint') },
]);

const canEditDocuments = computed(() => {
  const s = submission.value?.status;
  return !s || s === 'draft' || s === 'rejected';
});

const hasIdentityDoc = computed(() => {
  const docs = submission.value?.documents ?? [];
  return docs.some((d) => d.type === 'id_card' || d.type === 'passport');
});

function docByType(type: string) {
  return submission.value?.documents?.find((d) => d.type === type);
}

async function loadStatus() {
  const res = await api.get('/manage/system/profile/kyc');
  const data = res.data as {
    kyc_level?: string;
    onboarding_step?: number;
    email_verified?: boolean;
    has_phone?: boolean;
    submission?: KycSubmission | null;
  };
  kycLevel.value = data.kyc_level ?? 'level_0';
  onboardingStep.value = data.onboarding_step ?? 0;
  emailVerified.value = !!data.email_verified;
  hasPhone.value = !!data.has_phone;
  submission.value = data.submission ?? null;
  if (authStore.user) {
    basicForm.value.name = authStore.user.name ?? '';
    basicForm.value.phone = (authStore.user as { phone?: string }).phone ?? '';
    basicForm.value.location = (authStore.user as { location?: string }).location ?? '';
  }
}

async function submitBasic() {
  saving.value = true;
  try {
    await api.post('/manage/system/profile/kyc/basic', basicForm.value);
    toast.success.default(t('system.kyc.messages.basicDone'));
    await loadStatus();
    await authStore.fetchUser();
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
}

async function submitContact() {
  saving.value = true;
  try {
    await api.post('/manage/system/profile/kyc/contact');
    toast.success.default(t('system.kyc.messages.contactDone'));
    await loadStatus();
    await authStore.fetchUser();
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
}

async function onFile(type: string, event: Event) {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('type', type);
  fd.append('document', file);
  saving.value = true;
  try {
    await api.post('/manage/system/profile/kyc/documents', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    toast.success.default(t('system.kyc.messages.documentUploaded'));
    await loadStatus();
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
    input.value = '';
  }
}

async function submitReview() {
  saving.value = true;
  try {
    await api.post('/manage/system/profile/kyc/submit');
    toast.success.default(t('system.kyc.messages.submitted'));
    await loadStatus();
  } catch (e) {
    toast.error.fromResponse(e);
  } finally {
    saving.value = false;
  }
}

onMounted(loadStatus);
</script>
