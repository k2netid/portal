<template>
  <ConsoleFormCard :title="t('member.portal.security.twoFactor.section', 'Two-factor authentication')">
    <div
      v-if="initializing"
      class="text-sm text-muted-foreground"
    >
      {{ t('member.portal.security.twoFactor.loading', 'Checking status…') }}
    </div>

    <div
      v-else-if="!status.globally_enabled"
      class="text-sm text-muted-foreground"
    >
      {{ t('member.portal.security.twoFactor.globallyDisabled', 'Two-factor authentication is not available on this site right now.') }}
    </div>

    <div
      v-else-if="!status.enabled"
      class="space-y-4"
    >
      <p class="text-sm text-muted-foreground">
        {{ t('member.portal.security.twoFactor.setupDesc', 'Add an authenticator app for an extra layer of protection when signing in.') }}
      </p>

      <p
        v-if="error"
        class="text-sm text-destructive"
      >
        {{ error }}
      </p>

      <template v-if="!qrDataUrl">
        <Button
          type="button"
          :disabled="generating"
          @click="generate"
        >
          {{ generating
            ? t('member.portal.security.twoFactor.generating', 'Generating…')
            : t('member.portal.security.twoFactor.enable', 'Enable 2FA') }}
        </Button>
      </template>

      <template v-else>
        <div class="flex flex-col sm:flex-row gap-6 items-start">
          <div class="rounded-xl border border-border bg-white p-3 shrink-0">
            <img
              :src="qrDataUrl"
              alt="2FA QR"
              class="w-40 h-40"
            >
          </div>
          <div class="space-y-3 flex-1 min-w-0">
            <p class="text-xs text-muted-foreground">
              {{ t('member.portal.security.twoFactor.manualSecret', 'Or enter this secret manually:') }}
            </p>
            <code class="block text-xs font-mono break-all rounded-lg bg-muted/40 px-3 py-2">{{ secret }}</code>

            <div
              v-if="backupCodes.length"
              class="space-y-2"
            >
              <p class="text-sm font-medium">
                {{ t('member.portal.security.twoFactor.backupCodes', 'Backup codes') }}
              </p>
              <p class="text-xs text-muted-foreground">
                {{ t('member.portal.security.twoFactor.backupCodesHint', 'Store these somewhere safe. Each code can be used once if you lose your authenticator.') }}
              </p>
              <ul class="grid grid-cols-2 gap-1 font-mono text-xs">
                <li
                  v-for="code in backupCodes"
                  :key="code"
                >
                  {{ code }}
                </li>
              </ul>
            </div>

            <label class="block space-y-1.5 text-sm">
              <span class="font-medium">{{ t('member.portal.security.twoFactor.verifyLabel', 'Enter 6-digit code') }}</span>
              <input
                v-model="verifyCode"
                type="text"
                inputmode="numeric"
                maxlength="6"
                autocomplete="one-time-code"
                class="w-full h-10 rounded-lg border border-border bg-background px-3 text-center tracking-[0.4em] font-mono"
                @input="verifyCode = verifyCode.replace(/\D/g, '')"
              >
            </label>
            <div class="flex flex-wrap gap-2">
              <Button
                type="button"
                :disabled="verifying || verifyCode.length !== 6"
                @click="verify"
              >
                {{ verifying
                  ? t('member.portal.security.twoFactor.verifying', 'Verifying…')
                  : t('member.portal.security.twoFactor.verify', 'Verify & enable') }}
              </Button>
              <Button
                type="button"
                variant="outline"
                :disabled="verifying"
                @click="cancelSetup"
              >
                {{ t('member.portal.security.twoFactor.cancel', 'Cancel') }}
              </Button>
            </div>
          </div>
        </div>
      </template>
    </div>

    <div
      v-else
      class="space-y-4"
    >
      <p class="text-sm text-emerald-600">
        {{ t('member.portal.security.twoFactor.enabledDesc', 'Your reader account is protected with two-factor authentication.') }}
      </p>
      <p
        v-if="status.enabled_at"
        class="text-xs text-muted-foreground"
      >
        {{ t('member.portal.security.twoFactor.enabledAt', 'Enabled:') }}
        {{ new Date(status.enabled_at).toLocaleString() }}
      </p>
      <p
        v-if="error"
        class="text-sm text-destructive"
      >
        {{ error }}
      </p>
      <p
        v-if="success"
        class="text-sm text-emerald-600"
      >
        {{ success }}
      </p>

      <div
        v-if="!showDisable && !showRegen"
        class="space-y-4 border-t border-border/60 pt-4"
      >
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="space-y-1">
            <p class="text-sm font-medium">
              {{ t('member.portal.security.twoFactor.backupCodes', 'Backup codes') }}
            </p>
            <p class="text-xs text-muted-foreground">
              {{ t('member.portal.security.twoFactor.backupCodesHint', 'Store these somewhere safe. Each code can be used once if you lose your authenticator.') }}
            </p>
          </div>
          <span class="text-xs font-mono rounded-lg border border-border px-2 py-1">
            {{ status.backup_codes_count ?? 0 }}
            {{ t('member.portal.security.twoFactor.codesRemaining', 'remaining') }}
          </span>
        </div>
        <div
          v-if="backupCodes.length"
          class="rounded-xl border border-border bg-muted/20 p-4 space-y-3"
        >
          <ul class="grid grid-cols-2 gap-1 font-mono text-xs">
            <li
              v-for="code in backupCodes"
              :key="code"
            >
              {{ code }}
            </li>
          </ul>
          <p class="text-xs text-amber-600">
            {{ t('member.portal.security.twoFactor.saveWarning', 'Save these codes now — they will not be shown again.') }}
          </p>
          <Button
            type="button"
            variant="outline"
            size="sm"
            @click="backupCodes = []"
          >
            {{ t('member.portal.security.twoFactor.done', 'Done') }}
          </Button>
        </div>
        <div class="flex flex-wrap gap-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            @click="showRegen = true"
          >
            {{ t('member.portal.security.twoFactor.regenerate', 'Regenerate backup codes') }}
          </Button>
          <Button
            type="button"
            variant="outline"
            class="border-destructive/40 text-destructive"
            @click="showDisable = true"
          >
            {{ t('member.portal.security.twoFactor.disable', 'Disable 2FA') }}
          </Button>
        </div>
      </div>

      <form
        v-else-if="showRegen"
        class="space-y-3 max-w-sm border-t border-border/60 pt-4"
        @submit.prevent="regenerate"
      >
        <p class="text-sm text-muted-foreground">
          {{ t('member.portal.security.twoFactor.regenerateConfirm', 'Enter your password to generate new backup codes. Old codes will stop working.') }}
        </p>
        <input
          v-model="regenPassword"
          type="password"
          required
          autocomplete="current-password"
          class="w-full h-10 rounded-lg border border-border bg-background px-3"
        >
        <div class="flex flex-wrap gap-2">
          <Button
            type="submit"
            variant="outline"
            :disabled="regenerating"
          >
            {{ regenerating
              ? t('member.portal.security.twoFactor.regenerating', 'Regenerating…')
              : t('member.portal.security.twoFactor.regenerateSubmit', 'Regenerate codes') }}
          </Button>
          <Button
            type="button"
            variant="ghost"
            :disabled="regenerating"
            @click="showRegen = false; regenPassword = ''; error = ''"
          >
            {{ t('member.portal.security.twoFactor.cancel', 'Cancel') }}
          </Button>
        </div>
      </form>

      <form
        v-else
        class="space-y-3 max-w-sm border-t border-border/60 pt-4"
        @submit.prevent="disable"
      >
        <p class="text-sm text-muted-foreground">
          {{ t('member.portal.security.twoFactor.disableConfirm', 'Enter your password to disable two-factor authentication.') }}
        </p>
        <input
          v-model="disablePassword"
          type="password"
          required
          autocomplete="current-password"
          class="w-full h-10 rounded-lg border border-border bg-background px-3"
        >
        <div class="flex flex-wrap gap-2">
          <Button
            type="submit"
            variant="outline"
            class="border-destructive text-destructive"
            :disabled="disabling"
          >
            {{ t('member.portal.security.twoFactor.disableConfirmSubmit', 'Confirm disable') }}
          </Button>
          <Button
            type="button"
            variant="ghost"
            :disabled="disabling"
            @click="showDisable = false; disablePassword = ''; error = ''"
          >
            {{ t('member.portal.security.twoFactor.cancel', 'Cancel') }}
          </Button>
        </div>
      </form>
    </div>
  </ConsoleFormCard>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { isAxiosError } from 'axios';
import QRCode from 'qrcode';
import { Button } from '@/modules/Layout/views/themes/sarangenge/ui';
import { useMemberStore, type MemberTwoFactorStatus } from '@/modules/Member/stores/member';
import { ConsoleFormCard } from '@/shared/components/shell';

const { t } = useI18n();
const memberStore = useMemberStore();

const initializing = ref(true);
const generating = ref(false);
const verifying = ref(false);
const disabling = ref(false);
const regenerating = ref(false);
const error = ref('');
const success = ref('');

const status = ref<MemberTwoFactorStatus>({
    globally_enabled: false,
    enabled: false,
    enabled_at: null,
    backup_codes_count: 0,
});

const qrDataUrl = ref<string | null>(null);
const secret = ref('');
const backupCodes = ref<string[]>([]);
const verifyCode = ref('');
const showDisable = ref(false);
const showRegen = ref(false);
const disablePassword = ref('');
const regenPassword = ref('');

const apiError = (err: unknown, fallback: string): string => {
    if (isAxiosError(err)) {
        return String(err.response?.data?.message || fallback);
    }
    return fallback;
};

const refreshStatus = async (): Promise<void> => {
    status.value = await memberStore.fetchTwoFactorStatus();
};

onMounted(async () => {
    try {
        await refreshStatus();
    } catch {
        status.value = { globally_enabled: false, enabled: false };
    } finally {
        initializing.value = false;
    }
});

const cancelSetup = (): void => {
    qrDataUrl.value = null;
    secret.value = '';
    backupCodes.value = [];
    verifyCode.value = '';
    error.value = '';
};

const generate = async (): Promise<void> => {
    generating.value = true;
    error.value = '';
    try {
        const result = await memberStore.generateTwoFactor();
        secret.value = result.secret;
        backupCodes.value = result.backup_codes ?? [];
        qrDataUrl.value = await QRCode.toDataURL(result.qr_code_url);
    } catch (err: unknown) {
        error.value = apiError(err, t('member.portal.security.twoFactor.generateFailed', 'Could not start 2FA setup.'));
    } finally {
        generating.value = false;
    }
};

const verify = async (): Promise<void> => {
    verifying.value = true;
    error.value = '';
    try {
        await memberStore.verifyTwoFactor(verifyCode.value);
        cancelSetup();
        await refreshStatus();
        success.value = t('member.portal.security.twoFactor.enabledSuccess', 'Two-factor authentication is now enabled.');
    } catch (err: unknown) {
        error.value = apiError(err, t('member.portal.security.twoFactor.verifyFailed', 'Invalid verification code.'));
        verifyCode.value = '';
    } finally {
        verifying.value = false;
    }
};

const disable = async (): Promise<void> => {
    disabling.value = true;
    error.value = '';
    try {
        await memberStore.disableTwoFactor(disablePassword.value);
        showDisable.value = false;
        disablePassword.value = '';
        backupCodes.value = [];
        await refreshStatus();
        success.value = t('member.portal.security.twoFactor.disabledSuccess', 'Two-factor authentication has been disabled.');
    } catch (err: unknown) {
        error.value = apiError(err, t('member.portal.security.twoFactor.disableFailed', 'Could not disable 2FA.'));
    } finally {
        disabling.value = false;
    }
};

const regenerate = async (): Promise<void> => {
    regenerating.value = true;
    error.value = '';
    success.value = '';
    try {
        const result = await memberStore.regenerateTwoFactorBackupCodes(regenPassword.value);
        backupCodes.value = result.backup_codes ?? [];
        showRegen.value = false;
        regenPassword.value = '';
        await refreshStatus();
        success.value = t('member.portal.security.twoFactor.regeneratedSuccess', 'New backup codes generated. Save them now.');
    } catch (err: unknown) {
        error.value = apiError(err, t('member.portal.security.twoFactor.regenerateFailed', 'Could not regenerate backup codes.'));
    } finally {
        regenerating.value = false;
    }
};
</script>
