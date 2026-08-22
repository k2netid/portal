<template>
  <Dialog
    :open="isOpen"
    @update:open="v => { if(!v) $emit('close') }"
  >
    <DialogContent class="!p-0 !gap-0 max-w-xl h-[620px] max-h-[92vh] flex flex-col overflow-hidden rounded-2xl border border-border/80 bg-card shadow-2xl [&>button[aria-label=Close]]:hidden">
      <!-- Header -->
      <div class="h-12 px-4 bg-muted/40 border-b border-border/40 flex items-center justify-between select-none shrink-0">
        <DialogTitle class="text-xs font-bold text-foreground flex items-center gap-2">
          <Mail class="w-3.5 h-3.5 text-primary" />
          <span>{{ $t('system.mail.accounts.title') }}</span>
        </DialogTitle>

        <Button
          variant="ghost"
          size="icon"
          class="h-7 w-7 text-muted-foreground hover:text-foreground rounded-lg"
          @click="$emit('close')"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Main Body -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar min-h-0">
        <!-- Connected Accounts List Strip -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('system.mail.accounts.connectedAccounts') }} ({{ accounts.length }})
            </h4>

            <Button
              v-if="capabilities.can_manage_multi || accounts.length === 0"
              variant="outline"
              size="sm"
              class="h-6 text-[11px] px-2 gap-1"
              @click="startNewAccount"
            >
              <Plus class="w-3 h-3" />
              <span>{{ $t('system.mail.accounts.addAccount') }}</span>
            </Button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div
              v-for="acc in accounts"
              :key="acc.id"
              :class="[
                'p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-2',
                selectedAccountId === acc.id
                  ? 'border-primary bg-primary/5 ring-1 ring-primary/30'
                  : 'border-border/60 bg-muted/20 hover:bg-muted/40'
              ]"
              @click="selectAccountToEdit(acc)"
            >
              <div class="flex items-center gap-2 min-w-0 flex-1">
                <div class="w-7 h-7 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                  {{ (acc.name || acc.email || 'M').charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-1.5">
                    <p class="text-xs font-semibold text-foreground truncate">{{ acc.name }}</p>
                    <Star v-if="acc.is_default" class="w-3 h-3 fill-amber-400 text-amber-400 shrink-0" title="Default Mailbox" />
                  </div>
                  <p class="text-[10px] text-muted-foreground truncate">{{ acc.email }}</p>
                </div>
              </div>

              <span
                :class="[
                  'px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider shrink-0',
                  acc.account_type === 'custom_personal' ? 'bg-amber-500/10 text-amber-600 border border-amber-500/20' : 'bg-muted text-muted-foreground'
                ]"
              >
                {{ acc.account_type === 'custom_personal' ? 'Custom' : 'Global' }}
              </span>
            </div>
          </div>
        </div>

        <Separator class="my-2" />

        <!-- Account Form -->
        <form class="space-y-4" @submit.prevent="handleSave">
          <!-- Account Type Mode Selector -->
          <div class="space-y-2 p-3 rounded-xl bg-muted/20 border border-border/40">
            <h4 class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">
              {{ $t('system.mail.accounts.integrationMode') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <label
                :class="[
                  'p-3 rounded-xl border cursor-pointer flex flex-col gap-1 transition-all',
                  formData.account_type === 'system_global'
                    ? 'border-primary bg-primary/5 ring-1 ring-primary/30'
                    : 'border-border/60 bg-card hover:bg-muted/30'
                ]"
              >
                <div class="flex items-center gap-2">
                  <input
                    v-model="formData.account_type"
                    type="radio"
                    value="system_global"
                    class="text-primary focus:ring-primary h-3.5 w-3.5"
                  >
                  <span class="text-xs font-bold text-foreground">{{ $t('system.mail.accounts.inheritGlobal') }}</span>
                </div>
                <p class="text-[10px] text-muted-foreground pl-5.5">
                  {{ $t('system.mail.accounts.inheritGlobalDesc') }}
                </p>
              </label>

              <label
                :class="[
                  'p-3 rounded-xl border cursor-pointer flex flex-col gap-1 transition-all',
                  formData.account_type === 'custom_personal'
                    ? 'border-primary bg-primary/5 ring-1 ring-primary/30'
                    : 'border-border/60 bg-card hover:bg-muted/30',
                  !capabilities.can_manage_personal ? 'opacity-50 cursor-not-allowed' : ''
                ]"
              >
                <div class="flex items-center gap-2">
                  <input
                    v-model="formData.account_type"
                    type="radio"
                    value="custom_personal"
                    :disabled="!capabilities.can_manage_personal"
                    class="text-primary focus:ring-primary h-3.5 w-3.5"
                  >
                  <span class="text-xs font-bold text-foreground">{{ $t('system.mail.accounts.customPersonal') }}</span>
                </div>
                <p class="text-[10px] text-muted-foreground pl-5.5">
                  {{ $t('system.mail.accounts.customPersonalDesc') }}
                </p>
              </label>
            </div>
          </div>

          <!-- Basic Info -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">{{ $t('system.mail.accounts.accountName') }} *</label>
              <Input
                v-model="formData.name"
                placeholder="e.g. Work Mail, Support Desk"
                class="h-8 text-xs"
                required
              />
            </div>

            <div class="space-y-1">
              <label class="text-xs font-medium text-foreground">{{ $t('system.mail.accounts.emailAddress') }} *</label>
              <Input
                v-model="formData.email"
                type="email"
                placeholder="name@example.com"
                class="h-8 text-xs"
                required
              />
            </div>
          </div>

          <!-- Custom SMTP & IMAP Credentials (Rendered only when Custom Personal is chosen) -->
          <div v-if="formData.account_type === 'custom_personal'" class="space-y-3 p-3 rounded-xl bg-amber-500/5 border border-amber-500/20">
            <div class="flex items-center justify-between">
              <h4 class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                <Shield class="w-3 h-3" />
                <span>Custom SMTP & IMAP Server</span>
              </h4>

              <Button
                type="button"
                variant="outline"
                size="sm"
                class="h-6 text-[10px] gap-1 border-amber-500/30 text-amber-600 dark:text-amber-400 hover:bg-amber-500/10"
                :disabled="testingHandshake || !formData.smtp_host"
                @click="handleTestConnection"
              >
                <RefreshCw :class="['w-2.5 h-2.5', testingHandshake ? 'animate-spin' : '']" />
                <span>Test Connection</span>
              </Button>
            </div>

            <!-- SMTP Config -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <div class="space-y-1 sm:col-span-2">
                <label class="text-[11px] font-medium text-foreground">SMTP Host</label>
                <Input v-model="formData.smtp_host" placeholder="smtp.gmail.com" class="h-7 text-xs font-mono" />
              </div>
              <div class="space-y-1">
                <label class="text-[11px] font-medium text-foreground">SMTP Port</label>
                <Input v-model.number="formData.smtp_port" type="number" placeholder="587" class="h-7 text-xs font-mono" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <div class="space-y-1">
                <label class="text-[11px] font-medium text-foreground">SMTP Username</label>
                <Input v-model="formData.smtp_username" placeholder="user@gmail.com" class="h-7 text-xs font-mono" />
              </div>
              <div class="space-y-1">
                <label class="text-[11px] font-medium text-foreground">SMTP Password / App Password</label>
                <Input v-model="formData.smtp_password" type="password" placeholder="••••••••" class="h-7 text-xs font-mono" />
              </div>
            </div>

            <!-- IMAP Config -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-2 border-t border-amber-500/20">
              <div class="space-y-1 sm:col-span-2">
                <label class="text-[11px] font-medium text-foreground">IMAP Host</label>
                <Input v-model="formData.imap_host" placeholder="imap.gmail.com" class="h-7 text-xs font-mono" />
              </div>
              <div class="space-y-1">
                <label class="text-[11px] font-medium text-foreground">IMAP Port</label>
                <Input v-model.number="formData.imap_port" type="number" placeholder="993" class="h-7 text-xs font-mono" />
              </div>
            </div>
          </div>

          <!-- Signature & Options -->
          <div class="space-y-2">
            <label class="text-xs font-medium text-foreground">{{ $t('system.mail.accounts.signature') }}</label>
            <Textarea
              v-model="formData.signature"
              placeholder="e.g. Best regards, Jane Doe - Product Manager"
              rows="2"
              class="text-xs"
            />
          </div>

          <div class="flex items-center justify-between pt-1">
            <div>
              <p class="text-xs font-medium text-foreground">{{ $t('system.mail.accounts.makeDefault') }}</p>
              <p class="text-[10px] text-muted-foreground">{{ $t('system.mail.accounts.makeDefaultDesc') }}</p>
            </div>
            <Switch v-model="formData.is_default" />
          </div>
        </form>
      </div>

      <!-- Footer -->
      <div class="h-12 px-4 bg-muted/30 border-t border-border/40 flex items-center justify-between gap-2 shrink-0">
        <Button
          v-if="selectedAccountId && accounts.length > 1"
          variant="ghost"
          size="sm"
          class="h-8 text-xs text-destructive hover:bg-destructive/10"
          @click="handleDelete"
        >
          <Trash2 class="w-3.5 h-3.5 mr-1" />
          <span>{{ $t('common.actions.delete') }}</span>
        </Button>
        <div v-else />

        <div class="flex items-center gap-2">
          <Button
            type="button"
            variant="ghost"
            size="sm"
            class="h-8 text-xs"
            @click="$emit('close')"
          >
            {{ $t('common.actions.cancel') }}
          </Button>
          <Button
            type="button"
            size="sm"
            class="h-8 text-xs font-semibold px-4 shadow-xs"
            :disabled="!formData.name?.trim() || !formData.email?.trim() || saving"
            @click="handleSave"
          >
            {{ selectedAccountId ? $t('common.actions.save') : $t('system.mail.accounts.connect') }}
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import {
  Mail,
  X,
  Plus,
  Star,
  Shield,
  Trash2,
  RefreshCw,
} from 'lucide-vue-next';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  Button,
  Input,
  Textarea,
  Switch,
  Separator,
} from '@/shared/components/ui';
import type { MailAccount, MailAccountCapabilities } from '@/modules/Core/System/composables/useMailClient';

const props = defineProps<{
    isOpen: boolean;
    accounts: MailAccount[];
    capabilities: MailAccountCapabilities;
    saving?: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', payload: Partial<MailAccount>, id?: string): void;
    (e: 'delete', id: string): void;
    (e: 'test-connection', host: string, port: number): void;
}>();

const selectedAccountId = ref<string | null>(null);
const testingHandshake = ref(false);

const formData = ref<{
    name: string;
    email: string;
    account_type: 'system_global' | 'custom_personal';
    smtp_host: string;
    smtp_port: number;
    smtp_username: string;
    smtp_password: string;
    smtp_encryption: 'tls' | 'ssl' | 'null';
    imap_host: string;
    imap_port: number;
    imap_username: string;
    imap_password: string;
    imap_encryption: 'ssl' | 'tls' | 'null';
    is_default: boolean;
    is_active: boolean;
    signature: string;
}>({
    name: '',
    email: '',
    account_type: 'system_global',
    smtp_host: '',
    smtp_port: 587,
    smtp_username: '',
    smtp_password: '',
    smtp_encryption: 'tls',
    imap_host: '',
    imap_port: 993,
    imap_username: '',
    imap_password: '',
    imap_encryption: 'ssl',
    is_default: false,
    is_active: true,
    signature: '',
});

const selectAccountToEdit = (acc: MailAccount) => {
    selectedAccountId.value = acc.id;
    formData.value = {
        name: acc.name || '',
        email: acc.email || '',
        account_type: acc.account_type || 'system_global',
        smtp_host: acc.smtp_host || '',
        smtp_port: acc.smtp_port || 587,
        smtp_username: acc.smtp_username || '',
        smtp_password: '',
        smtp_encryption: acc.smtp_encryption || 'tls',
        imap_host: acc.imap_host || '',
        imap_port: acc.imap_port || 993,
        imap_username: acc.imap_username || '',
        imap_password: '',
        imap_encryption: acc.imap_encryption || 'ssl',
        is_default: Boolean(acc.is_default),
        is_active: Boolean(acc.is_active),
        signature: acc.signature || '',
    };
};

const startNewAccount = () => {
    selectedAccountId.value = null;
    formData.value = {
        name: '',
        email: '',
        account_type: 'system_global',
        smtp_host: '',
        smtp_port: 587,
        smtp_username: '',
        smtp_password: '',
        smtp_encryption: 'tls',
        imap_host: '',
        imap_port: 993,
        imap_username: '',
        imap_password: '',
        imap_encryption: 'ssl',
        is_default: props.accounts.length === 0,
        is_active: true,
        signature: '',
    };
};

watch(() => props.isOpen, (open) => {
    if (open) {
        if (props.accounts.length > 0) {
            const target = props.accounts.find(a => a.is_default) || props.accounts[0];
            if (target) {
                selectAccountToEdit(target);
            }
        } else {
            startNewAccount();
        }
    }
});

const handleSave = () => {
    if (!formData.value.name?.trim() || !formData.value.email?.trim()) return;
    emit('save', { ...formData.value }, selectedAccountId.value || undefined);
};

const handleDelete = () => {
    if (selectedAccountId.value && confirm('Are you sure you want to disconnect this mailbox account?')) {
        emit('delete', selectedAccountId.value);
    }
};

const handleTestConnection = async () => {
    if (!formData.value.smtp_host || !formData.value.smtp_port) return;
    testingHandshake.value = true;
    try {
        emit('test-connection', formData.value.smtp_host, formData.value.smtp_port);
    } finally {
        setTimeout(() => {
            testingHandshake.value = false;
        }, 1000);
    }
};
</script>
