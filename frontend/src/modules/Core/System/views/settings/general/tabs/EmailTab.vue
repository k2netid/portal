<template>
  <div class="space-y-6">
    <!-- Presets Quick Bar -->
    <div class="p-4 rounded-xl bg-card border border-border/60 shadow-sm space-y-3">
      <div class="flex items-center justify-between">
        <div>
          <h4 class="text-xs font-semibold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
            <Zap class="w-3.5 h-3.5 text-amber-500" />
            {{ $t('system.settings.email.quick_presets') }}
          </h4>
          <p class="text-xs text-muted-foreground mt-0.5">
            {{ $t('system.settings.email.quick_presets_desc') }}
          </p>
        </div>
      </div>
      <div class="flex flex-wrap gap-2">
        <Button
          v-for="preset in presets"
          :key="preset.id"
          type="button"
          variant="outline"
          size="sm"
          class="text-xs h-8 gap-1.5 hover:border-primary/50 transition-colors"
          @click="applyPreset(preset)"
        >
          <span>{{ preset.name }}</span>
        </Button>
      </div>
    </div>

    <!-- Sender Identity Group -->
    <SettingGroup
      :title="$t('system.settings.groups.identity.title')"
      :description="$t('system.settings.groups.identity.description')"
      :icon="User"
      color="indigo"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.labels.mail_from_name') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.descriptions.mail_from_name') }}
          </p>
          <Input
            :model-value="(formData.mail_from_name as string) || ''"
            :placeholder="$t('system.settings.labels.mail_from_name')"
            @update:model-value="v => updateField('mail_from_name', v)"
          />
          <p v-if="errors?.mail_from_name" class="text-xs text-destructive mt-1">
            {{ errors.mail_from_name[0] }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.labels.mail_from_address') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.descriptions.mail_from_address') }}
          </p>
          <Input
            :model-value="(formData.mail_from_address as string) || ''"
            type="email"
            placeholder="noreply@domain.com"
            @update:model-value="v => updateField('mail_from_address', v)"
          />
          <p v-if="errors?.mail_from_address" class="text-xs text-destructive mt-1">
            {{ errors.mail_from_address[0] }}
          </p>
        </div>
      </div>
    </SettingGroup>

    <!-- SMTP Transport Connection Group -->
    <SettingGroup
      :title="$t('system.settings.groups.smtp.title')"
      :description="$t('system.settings.groups.smtp.description')"
      :icon="MailIcon"
      color="indigo"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Mail Driver -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.labels.mail_driver') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.descriptions.mail_driver') }}
          </p>
          <Select
            :model-value="(formData.mail_driver as string) || 'smtp'"
            @update:model-value="v => updateField('mail_driver', v)"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('system.settings.labels.mail_driver')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="smtp">SMTP (Recommended)</SelectItem>
              <SelectItem value="sendmail">Sendmail / Local Server</SelectItem>
              <SelectItem value="mailgun">Mailgun API</SelectItem>
              <SelectItem value="ses">Amazon SES</SelectItem>
              <SelectItem value="postmark">Postmark</SelectItem>
              <SelectItem value="log">Log / Development (No actual send)</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Mail Host -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.labels.mail_host') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.descriptions.mail_host') }}
          </p>
          <Input
            :model-value="(formData.mail_host as string) || ''"
            :disabled="formData.mail_driver === 'log'"
            placeholder="smtp.mailtrap.io"
            @update:model-value="v => updateField('mail_host', v)"
          />
        </div>

        <!-- Mail Port -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.labels.mail_port') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.descriptions.mail_port') }}
          </p>
          <Input
            :model-value="String(formData.mail_port || 587)"
            type="number"
            :disabled="formData.mail_driver === 'log'"
            placeholder="587"
            @update:model-value="v => updateField('mail_port', Number(v) || 587)"
          />
        </div>

        <!-- Encryption -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.labels.mail_encryption') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.descriptions.mail_encryption') }}
          </p>
          <Select
            :model-value="(formData.mail_encryption as string) || 'tls'"
            :disabled="formData.mail_driver === 'log'"
            @update:model-value="v => updateField('mail_encryption', v)"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('system.settings.labels.mail_encryption')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="tls">TLS / STARTTLS (Port 587 / 2525)</SelectItem>
              <SelectItem value="ssl">SSL / SMTPS (Port 465)</SelectItem>
              <SelectItem value="none">None (Port 25 / Local)</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Username -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.labels.mail_username') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.descriptions.mail_username') }}
          </p>
          <Input
            :model-value="(formData.mail_username as string) || ''"
            :disabled="formData.mail_driver === 'log'"
            placeholder="username / api_key"
            @update:model-value="v => updateField('mail_username', v)"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-foreground mb-1">
            {{ $t('system.settings.labels.mail_password') }}
          </label>
          <p class="text-xs text-muted-foreground mb-2">
            {{ $t('system.settings.descriptions.mail_password') }}
          </p>
          <div class="relative">
            <Input
              :model-value="(formData.mail_password as string) || ''"
              :type="showPassword ? 'text' : 'password'"
              :disabled="formData.mail_driver === 'log'"
              placeholder="••••••••••••"
              class="pr-10"
              @update:model-value="v => updateField('mail_password', v)"
            />
            <button 
              type="button"
              class="absolute right-0 top-0 h-full px-3 text-muted-foreground hover:text-foreground transition-colors"
              tabindex="-1"
              @click="showPassword = !showPassword"
            >
              <EyeOff v-if="showPassword" class="w-4 h-4" />
              <Eye v-else class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Action & Validation Footer -->
      <template #footer>
        <div class="flex flex-col gap-4">
          <div class="flex flex-wrap items-center gap-3">
            <!-- Config Validation -->
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="gap-1.5"
              :disabled="validatingConfig"
              @click="$emit('validate-config')"
            >
              <Loader2 v-if="validatingConfig" class="w-3.5 h-3.5 animate-spin" />
              <span>{{ validatingConfig ? $t('system.settings.emailTest.validating') : $t('system.settings.emailTest.validate') }}</span>
            </Button>
            <div v-if="configValidation" class="text-xs font-medium">
              <span v-if="configValidation.valid" class="text-emerald-600 dark:text-emerald-400">✓ {{ $t('system.settings.emailTest.valid') }}</span>
              <span v-else class="text-destructive">✗ {{ $t('system.settings.emailTest.invalid') }}</span>
            </div>

            <!-- SMTP Connection Test -->
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="gap-1.5"
              :disabled="testingConnection || formData.mail_driver === 'log'"
              @click="$emit('test-connection')"
            >
              <Loader2 v-if="testingConnection" class="w-3.5 h-3.5 animate-spin text-primary" />
              <Wifi v-else class="w-3.5 h-3.5" />
              <span>{{ testingConnection ? $t('system.settings.emailTest.testing') : $t('system.settings.emailTest.testConnection') }}</span>
            </Button>
            <div v-if="connectionResult" class="text-xs font-medium">
              <span v-if="connectionResult.connected" class="text-emerald-600 dark:text-emerald-400">
                ✓ {{ $t('system.settings.emailTest.connected', { host: connectionResult.host, port: connectionResult.port }) }}
              </span>
              <span v-else class="text-destructive">
                ✗ {{ $t('system.settings.emailTest.failed') }}
              </span>
            </div>
          </div>

          <!-- Validation Errors/Warnings -->
          <div
            v-if="configValidation && (!configValidation.valid || (configValidation.warnings && configValidation.warnings.length > 0))"
            class="p-3 bg-muted/60 border border-border/40 rounded-lg space-y-2"
          >
            <div v-if="configValidation.errors && configValidation.errors.length > 0">
              <p class="text-xs font-semibold text-destructive mb-1">
                {{ $t('system.settings.emailTest.errors') }}:
              </p>
              <ul class="text-xs text-destructive list-disc list-inside space-y-0.5">
                <li v-for="error in configValidation.errors" :key="error">
                  {{ error }}
                </li>
              </ul>
            </div>
            <div v-if="configValidation.warnings && configValidation.warnings.length > 0">
              <p class="text-xs font-semibold text-amber-500 mb-1">
                {{ $t('system.settings.emailTest.warnings') }}:
              </p>
              <ul class="text-xs text-amber-600 dark:text-amber-400 list-disc list-inside space-y-0.5">
                <li v-for="warning in configValidation.warnings" :key="warning">
                  {{ warning }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </template>
    </SettingGroup>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from '@/shared/composables/useToast';
import {
  Mail as MailIcon,
  User,
  Zap,
  Eye,
  EyeOff,
  Loader2,
  Wifi,
} from 'lucide-vue-next';
import SettingGroup from '@/modules/Core/System/components/settings/SettingGroup.vue';
import {
  Button,
  Input,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/shared/components/ui';

interface Setting {
    id: string;
    key: string;
    value: unknown;
    type: string;
    group: string;
}

interface ConfigValidation {
    valid: boolean;
    errors?: string[];
    warnings?: string[];
}

interface ConnectionResult {
    connected: boolean;
    host?: string;
    port?: string;
}

interface Props {
    settings: Setting[];
    formData: Record<string, unknown>;
    validatingConfig?: boolean;
    configValidation?: ConfigValidation | null;
    testingConnection?: boolean;
    connectionResult?: ConnectionResult | null;
    errors?: Record<string, string[]>;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:formData', value: Record<string, unknown>): void;
    (e: 'validate-config'): void;
    (e: 'test-connection'): void;
}>();

const toast = useToast();
const showPassword = ref(false);

const updateField = (key: string, value: unknown) => {
    emit('update:formData', { ...props.formData, [key]: value });
};

interface Preset {
    id: string;
    name: string;
    driver: string;
    host: string;
    port: number;
    encryption: string;
}

const presets: Preset[] = [
    {
        id: 'mailtrap',
        name: 'Mailtrap',
        driver: 'smtp',
        host: 'sandbox.smtp.mailtrap.io',
        port: 2525,
        encryption: 'tls',
    },
    {
        id: 'gmail',
        name: 'Gmail SMTP',
        driver: 'smtp',
        host: 'smtp.gmail.com',
        port: 587,
        encryption: 'tls',
    },
    {
        id: 'outlook',
        name: 'Outlook 365',
        driver: 'smtp',
        host: 'smtp.office365.com',
        port: 587,
        encryption: 'tls',
    },
    {
        id: 'mailgun',
        name: 'Mailgun',
        driver: 'smtp',
        host: 'smtp.mailgun.org',
        port: 587,
        encryption: 'tls',
    },
    {
        id: 'ses',
        name: 'Amazon SES',
        driver: 'smtp',
        host: 'email-smtp.us-east-1.amazonaws.com',
        port: 587,
        encryption: 'tls',
    },
    {
        id: 'log',
        name: 'Log / Local',
        driver: 'log',
        host: 'localhost',
        port: 1025,
        encryption: 'none',
    },
];

const applyPreset = (preset: Preset) => {
    emit('update:formData', {
        ...props.formData,
        mail_driver: preset.driver,
        mail_host: preset.host,
        mail_port: preset.port,
        mail_encryption: preset.encryption,
    });
    toast.success.action(`Applied ${preset.name} preset`);
};
</script>
