<template>
  <div class="space-y-6">
    <!-- Initial Loading State -->
    <div
      v-if="initializing"
      class="flex flex-col items-center justify-center py-12 space-y-4"
    >
      <Loader2 class="h-8 w-8 text-muted-foreground" />
      <p class="text-sm text-muted-foreground">
        {{ $t('common.actions.verifying') }}
      </p>
    </div>

    <template v-else>
      <!-- Global Disabled Warning -->
      <Alert
        v-if="status.global_enabled === false"
        variant="destructive"
      >
        <AlertCircle class="h-4 w-4" />
        <AlertTitle>{{ $t('system.security.twoFactor.globallyDisabledTitle') }}</AlertTitle>
        <AlertDescription>
          {{ $t('system.security.twoFactor.globallyDisabledDesc') }}
        </AlertDescription>
      </Alert>

      <template v-else>
        <!-- Setup / Enable 2FA Section -->
        <div
          v-if="!status.enabled"
          class="space-y-6"
        >
          <div class="space-y-2">
            <h3 class="text-lg font-medium">
              {{ $t('system.auth.twoFactor.setupTitle') }}
            </h3>
            <p class="text-sm text-muted-foreground">
              {{ $t('system.auth.twoFactor.setupDesc') }}
            </p>
          </div>

          <Alert
            v-if="status.required"
            variant="default"
            class="bg-warning/10 border-warning/20 text-warning"
          >
            <ShieldAlert class="h-4 w-4" />
            <AlertTitle>{{ $t('system.auth.twoFactor.requiredTitle') }}</AlertTitle>
            <AlertDescription>
              {{ $t('system.auth.twoFactor.requiredDesc') }}
            </AlertDescription>
          </Alert>

          <!-- Generate Flow -->
          <div
            v-if="!qrCodeUrl"
            class="flex flex-col items-center py-8 border-2 border-dashed rounded-xl border-muted"
          >
            <div class="bg-primary/10 p-4 rounded-full mb-4">
              <Smartphone class="h-8 w-8 text-primary" />
            </div>
            <Button
              :disabled="generating"
              @click="generateSecret"
            >
              <Loader2
                v-if="generating"
                class="mr-2 h-4 w-4"
              />
              {{ generating ? ('common.actions.generating') : ('system.auth.twoFactor.generateQR') }}
            </Button>
          </div>

          <!-- QR Code & Verify Flow -->
          <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start"
          >
            <div class="space-y-4 flex flex-col items-center bg-muted/30 p-6 rounded-xl border border-border">
              <div class="p-4 bg-white rounded-lg shadow-sm">
                <img
                  :src="qrCodeUrl"
                  alt="2FA QR Code"
                  class="w-48 h-48"
                >
              </div>
              <div class="text-center space-y-2">
                <p class="text-sm font-medium">
                  {{ $t('system.auth.twoFactor.scanQR') }}
                </p>
                <div class="flex items-center gap-2 text-xs font-mono bg-background px-3 py-1.5 rounded border border-border">
                  <span class="truncate max-w-[150px]">{{ secret }}</span>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-6 w-6"
                    @click="copySecret"
                  >
                    <Copy class="h-3 w-3" />
                  </Button>
                </div>
              </div>
            </div>

            <div class="space-y-6">
              <div class="space-y-2">
                <Label for="verify-code">{{ $t('system.auth.twoFactor.enterCode') }}</Label>
                <Input
                  id="verify-code"
                  v-model="verificationCode"
                  type="text"
                  maxlength="6"
                  :placeholder="t('common.placeholders.otp')"
                  class="text-center text-2xl tracking-[0.5em] font-mono"
                  @input="verificationCode = verificationCode.replace(/\D/g, '')"
                />
                <p class="text-xs text-muted-foreground">
                  {{ $t('system.auth.twoFactor.codeHelp') }}
                </p>
              </div>

              <div class="flex flex-col gap-2">
                <Button
                  :disabled="!verificationCode || verificationCode.length !== 6 || enabling"
                  class="w-full"
                  @click="enable2FA"
                >
                  <Loader2
                    v-if="enabling"
                    class="mr-2 h-4 w-4"
                  />
                  {{ enabling ? ('common.actions.verifying') : ('system.auth.twoFactor.enable') }}
                </Button>
                <Button
                  variant="ghost"
                  class="w-full"
                  @click="qrCodeUrl = null"
                >
                  {{ $t('common.actions.cancel') }}
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- Manage / Disable 2FA Section -->
        <div
          v-else
          class="space-y-8"
        >
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <h3 class="text-lg font-medium">
                  {{ $t('system.auth.twoFactor.enabledTitle') }}
                </h3>
                <Badge
                  variant="success"
                  class="bg-success/10 text-success border-success/20"
                >
                  {{ $t('common.status.active') }}
                </Badge>
              </div>
              <p class="text-sm text-muted-foreground">
                {{ $t('system.auth.twoFactor.enabledDesc') }}
              </p>
              <p
                v-if="status.enabled_at"
                class="text-xs text-muted-foreground"
              >
                {{ $t('system.auth.twoFactor.enabledAt') }}: {{ new Date(status.enabled_at).toLocaleString() }}
              </p>
            </div>
            <Button
              variant="destructive"
              ghost
              :disabled="status.required"
              @click="showDisableConfirm = true"
            >
              <Trash2 class="h-4 w-4 mr-2" />
              {{ $t('system.auth.twoFactor.disable') }}
            </Button>
          </div>

          <div class="h-px bg-border w-full" />

          <!-- Recovery Codes Section -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="space-y-1">
                <h4 class="text-md font-medium">
                  {{ $t('system.auth.twoFactor.recoveryTitle') }}
                </h4>
                <p class="text-sm text-muted-foreground">
                  {{ $t('system.auth.twoFactor.recoveryDesc') }}
                </p>
              </div>
              <div
                v-if="backupCodes.length === 0"
                class="flex items-center gap-2"
              >
                <Badge
                  variant="outline"
                  class="font-mono"
                >
                  {{ status.backup_codes_count }} {{ $t('system.auth.twoFactor.codesRemaining') }}
                </Badge>
                <Button
                  variant="outline"
                  size="sm"
                  @click="showRegenPassword = true"
                >
                  <RefreshCcw class="h-3 w-3 mr-2" />
                  {{ $t('system.auth.twoFactor.regenerate') }}
                </Button>
              </div>
            </div>

            <!-- codes display -->
            <div
              v-if="backupCodes.length > 0"
              class="bg-muted/30 border rounded-xl p-6 space-y-6"
            >
              <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <div
                  v-for="(code, index) in backupCodes"
                  :key="index"
                  class="bg-background border rounded px-3 py-2 text-center font-mono text-sm"
                >
                  {{ code }}
                </div>
              </div>
              <div class="flex items-center justify-between gap-4">
                <p class="text-xs text-warning font-medium">
                  <AlertTriangle class="h-3 w-3 inline mr-1" />
                  {{ $t('system.auth.twoFactor.saveWarning') }}
                </p>
                <div class="flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    @click="downloadBackupCodes"
                  >
                    <Download class="h-3 w-3 mr-2" />
                    {{ $t('common.actions.download') }}
                  </Button>
                  <Button
                    size="sm"
                    @click="backupCodes = []"
                  >
                    {{ $t('common.actions.done') }}
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <!-- Password Modals -->
          <Dialog
            v-if="showDisableConfirm"
            :open="showDisableConfirm"
            @update:open="showDisableConfirm = $event"
          >
            <DialogContent class="console-dialog-md">
              <DialogHeader>
                <DialogTitle>{{ $t('system.auth.twoFactor.disableTitle') }}</DialogTitle>
                <DialogDescription>
                  {{ $t('system.auth.twoFactor.disableConfirm') }}
                </DialogDescription>
              </DialogHeader>
              <form
                class="space-y-4"
                @submit.prevent="disable2FA"
              >
                <div class="space-y-2">
                  <Label for="disable-password">{{ $t('common.labels.password') }}</Label>
                  <Input 
                    id="disable-password" 
                    v-model="passwordConfirm" 
                    type="password" 
                    name="password"
                    autocomplete="current-password"
                    required 
                    autofocus 
                  />
                </div>
                <DialogFooter>
                  <Button
                    type="button"
                    variant="ghost"
                    @click="showDisableConfirm = false"
                  >
                    {{ $t('common.actions.cancel') }}
                  </Button>
                  <Button
                    type="submit"
                    variant="destructive"
                    :disabled="!passwordConfirm || disabling"
                  >
                    <Loader2
                      v-if="disabling"
                      class="mr-2 h-4 w-4"
                    />
                    {{ disabling ? ('common.actions.disabling') : ('system.auth.twoFactor.confirmDisable') }}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>

          <Dialog
            v-if="showRegenPassword"
            :open="showRegenPassword"
            @update:open="showRegenPassword = $event"
          >
            <DialogContent class="console-dialog-md">
              <DialogHeader>
                <DialogTitle>{{ $t('system.auth.twoFactor.regenTitle') }}</DialogTitle>
                <DialogDescription>
                  {{ $t('system.auth.twoFactor.regenDesc') }}
                </DialogDescription>
              </DialogHeader>
              <form
                class="space-y-4"
                @submit.prevent="regenerateBackupCodes"
              >
                <div class="space-y-2">
                  <Label for="regen-password">{{ $t('common.labels.password') }}</Label>
                  <Input 
                    id="regen-password" 
                    v-model="passwordConfirm" 
                    type="password" 
                    name="password"
                    autocomplete="current-password"
                    required 
                    autofocus 
                  />
                </div>
                <DialogFooter>
                  <Button
                    type="button"
                    variant="ghost"
                    @click="showRegenPassword = false"
                  >
                    {{ $t('common.actions.cancel') }}
                  </Button>
                  <Button
                    type="submit"
                    :disabled="!passwordConfirm || regenerating"
                  >
                    <Loader2
                      v-if="regenerating"
                      class="mr-2 h-4 w-4"
                    />
                    {{ regenerating ? ('common.actions.regenerating') : ('system.auth.twoFactor.confirmRegen') }}
                  </Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
        </div>
      </template>
    </template>
  </div>
</template>

<script setup lang="ts">
import { logger } from '@/shared/utils/logger';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import api from '@/engine/api/client';
import { parseSingleResponse } from '@/shared/utils/responseParser';
import QRCode from 'qrcode';
import toast from '@/shared/services/toastService';


// Shadcn Components
import {
    Button,
    Input,
    Label,
    Badge,
    Alert,
    AlertDescription,
    AlertTitle,
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/shared/components/ui';

// Icons
import {
  AlertCircle,
  AlertTriangle,
  Copy,
  Download,
  Loader2,
  RefreshCcw,
  ShieldAlert,
  Smartphone,
  Trash2,
} from 'lucide-vue-next';

interface TwoFactorStatus {
    enabled: boolean;
    required: boolean;
    backup_codes_count: number;
    enabled_at: string | null;
    global_enabled: boolean;
    [key: string]: unknown;
}

interface GenerateResponse {
    secret: string;
    qr_code_url?: string;
    backup_codes?: string[];
    [key: string]: unknown;
}

const { t } = useI18n();


const status = ref<TwoFactorStatus>({
    enabled: false,
    required: false,
    backup_codes_count: 0,
    enabled_at: null,
    global_enabled: true
});

const qrCodeUrl = ref<string | null>(null);
const secret = ref<string | null>(null);
const verificationCode = ref('');
const backupCodes = ref<string[]>([]);

const generating = ref(false);
const enabling = ref(false);
const disabling = ref(false);
const regenerating = ref(false);
const initializing = ref(true);

const showDisableConfirm = ref(false);
const showRegenPassword = ref(false);
const passwordConfirm = ref('');

const fetchStatus = async () => {
    try {
        const response = await api.get('/two-factor/status');
        const data = parseSingleResponse(response);
        status.value = data as TwoFactorStatus;
    } catch (error) {
        logger.error('Error fetching 2FA status:', error);
    } finally {
        initializing.value = false;
    }
};

const generateSecret = async () => {
    generating.value = true;
    qrCodeUrl.value = null;
    secret.value = null;
    verificationCode.value = '';

    try {
        const response = await api.post('/two-factor/generate');
        const data = parseSingleResponse(response) as GenerateResponse;
        
        secret.value = data.secret;
        
        if (data.qr_code_url) {
            qrCodeUrl.value = await QRCode.toDataURL(data.qr_code_url, {
                width: 256,
                margin: 2,
            });
        }

        if (data.backup_codes) {
            backupCodes.value = data.backup_codes;
        }
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            toast.error(t('common.messages.toast.error'), err.response?.data?.message || t('system.auth.twoFactor.messages.generateFailed'));
        } else {
            toast.error(t('common.messages.toast.error'), t('system.auth.twoFactor.messages.generateFailed'));
        }
    } finally {
        generating.value = false;
    }
};

const enable2FA = async () => {
    if (!verificationCode.value || verificationCode.value.length !== 6) {
        toast.error(t('system.auth.twoFactor.messages.validationErrorTitle'), t('system.auth.twoFactor.messages.validationCodeRequired'));
        return;
    }

    enabling.value = true;
    try {
        await api.post('/two-factor/verify', {
            code: verificationCode.value,
        });
        
        toast.success(t('common.status.success'), t('system.auth.twoFactor.messages.enableSuccess'));
        verificationCode.value = '';
        qrCodeUrl.value = null;
        secret.value = null;
        await fetchStatus();
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            toast.error(t('common.status.failed'), err.response?.data?.message || t('system.auth.messages.error'));
        } else {
            toast.error(t('common.status.failed'), t('system.auth.messages.error'));
        }
    } finally {
        enabling.value = false;
    }
};

const disable2FA = async () => {
    disabling.value = true;
    try {
        await api.post('/two-factor/disable', {
            password: passwordConfirm.value,
        });
        
        toast.success(t('common.status.success'), t('system.auth.twoFactor.messages.disableSuccess'));
        passwordConfirm.value = '';
        showDisableConfirm.value = false;
        backupCodes.value = [];
        await fetchStatus();
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            toast.error(t('common.status.failed'), err.response?.data?.message || t('system.auth.messages.error'));
        } else {
            toast.error(t('common.status.failed'), t('system.auth.messages.error'));
        }
    } finally {
        disabling.value = false;
    }
};

const regenerateBackupCodes = async () => {
    regenerating.value = true;
    try {
        const response = await api.post('/two-factor/regenerate-backup-codes', {
            password: passwordConfirm.value,
        });
        const data = parseSingleResponse(response) as { backup_codes?: string[] };
        
        if (data.backup_codes) {
            backupCodes.value = data.backup_codes;
        }
        toast.success(t('common.status.success'), t('system.auth.twoFactor.messages.regenerateSuccess'));
        passwordConfirm.value = '';
        showRegenPassword.value = false;
        await fetchStatus();
    } catch (error: unknown) {
        if (error && typeof error === 'object' && 'response' in error) {
            const err = error as { response?: { data?: { message?: string } } };
            toast.error(t('common.status.failed'), err.response?.data?.message || t('system.auth.messages.error'));
        } else {
            toast.error(t('common.status.failed'), t('system.auth.messages.error'));
        }
    } finally {
        regenerating.value = false;
    }
};

const copySecret = async () => {
    if (!secret.value) return;
    try {
        await navigator.clipboard.writeText(secret.value);
        toast.success(t('common.status.success'), t('system.auth.twoFactor.messages.copySuccess'));
    } catch {
        toast.error(t('common.status.failed'), t('common.messages.error.default'));
    }
};

const downloadBackupCodes = () => {
    if (backupCodes.value.length === 0) return;
    
    const content = `Recovery Codes for Two-Factor Authentication\n\n` +
        `Save these codes in a safe place:\n\n` +
        backupCodes.value.join('\n') +
        `\n\nGenerated: ${new Date().toLocaleString()}`;
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `recovery-codes-${new Date().toISOString().slice(0, 10)}.txt`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
};

onMounted(() => {
    fetchStatus();
});
</script>
